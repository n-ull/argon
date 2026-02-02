<?php

namespace Domain\Promoters\Commands;

use Domain\OrganizerManagement\Models\Organizer;
use Domain\Promoters\Actions\InvitePromoter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BulkInvitePromoters extends Command
{
    protected $signature = 'promoters:bulk-invite 
                            {organizer : The ID of the organizer} 
                            {file : Path to a file containing emails} 
                            {--commission_type=percentage : Commission type (fixed or percentage)} 
                            {--commission_value=10 : Commission value}';

    protected $description = 'Bulk invite promoters from a file';

    public function handle(InvitePromoter $invitePromoter)
    {
        $organizerId = $this->argument('organizer');
        $organizer = Organizer::find($organizerId);

        if (! $organizer) {
            $this->error("Organizer with ID {$organizerId} not found.");
            return Command::FAILURE;
        }

        $filePath = $this->argument('file');
        if (! file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return Command::FAILURE;
        }

        // Read the entire file content
        $content = file_get_contents($filePath);

        // Regex to extract emails
        $pattern = '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/';
        preg_match_all($pattern, $content, $matches);

        // Get unique emails
        $emails = array_unique($matches[0]);

        if (empty($emails)) {
            $this->warn("No emails found in file.");
            return Command::SUCCESS;
        }

        $commissionType = $this->option('commission_type');
        $commissionValue = $this->option('commission_value');

        if (! in_array($commissionType, ['fixed', 'percentage'])) {
            $this->error("Invalid commission type. Must be 'fixed' or 'percentage'.");
            return Command::FAILURE;
        }

        $this->info("Found ".count($emails)." unique emails. Processing invitations for organizer: {$organizer->name}");

        $invitedCount = 0;
        $errorCount = 0;
        $skippedCount = 0;

        foreach ($emails as $email) {
            // Further validation to be safe, though regex is pretty good
            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->warn("Skipping invalid email format after extraction: {$email}");
                $errorCount++;
                continue;
            }

            try {
                // Prepare data for the action
                $data = [
                    'email' => $email,
                    'commission_type' => $commissionType,
                    'commission_value' => $commissionValue,
                ];

                // The action handles checking for existing users/promoters and invitations
                // It throws ValidationException if issues found
                $invitePromoter->handle($organizer->id, $data);

                $this->info("Invited: {$email}");
                $invitedCount++;

            } catch (ValidationException $e) {
                // Extract the message
                $message = implode(', ', \Illuminate\Support\Arr::flatten($e->errors()));
                $this->line("Skipped {$email}: {$message}");
                $skippedCount++;
            } catch (\Exception $e) {
                $this->error("Error inviting {$email}: ".$e->getMessage());
                $errorCount++;
            }
        }

        $this->table(
            ['Metric', 'Count'],
            [
                ['Invited', $invitedCount],
                ['Skipped (Existing/Pending)', $skippedCount],
                ['Errors', $errorCount],
            ]
        );

        return Command::SUCCESS;
    }
}
