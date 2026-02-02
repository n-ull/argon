<?php

namespace Domain\Promoters\Commands;

use App\Models\User;
use Domain\OrganizerManagement\Models\Organizer;
use Domain\Promoters\Models\Promoter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class LinkExistingUsersToOrganizer extends Command
{
    protected $signature = 'promoters:link-existing 
                            {organizer : The ID of the organizer} 
                            {emails?* : List of emails to link} 
                            {--file= : Optional path to a file containing emails}';

    protected $description = 'Link existing users as promoters to an organizer without sending invitations';

    public function handle()
    {
        $organizerId = $this->argument('organizer');
        $organizer = Organizer::find($organizerId);

        if (! $organizer) {
            $this->error("Organizer with ID {$organizerId} not found.");
            return Command::FAILURE;
        }

        $emails = $this->argument('emails') ?? [];
        $filePath = $this->option('file');

        if ($filePath) {
            if (! file_exists($filePath)) {
                $this->error("File not found: {$filePath}");
                return Command::FAILURE;
            }

            // Read the entire file content
            $content = file_get_contents($filePath);

            // Regex to extract emails
            $pattern = '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/';
            preg_match_all($pattern, $content, $matches);

            $fileEmails = $matches[0] ?? [];
            $emails = array_merge($emails, $fileEmails);
        }

        $emails = array_unique(array_filter(array_map('trim', $emails)));

        // DEBUG
        // dump($emails);

        if (empty($emails)) {
            $this->warn("No emails provided (or found in file).");
            return Command::SUCCESS;
        }

        $this->info("Processing ".count($emails)." unique emails for organizer: {$organizer->name}");

        $linkedCount = 0;
        $notFoundCount = 0;
        $alreadyLinkedCount = 0;

        foreach ($emails as $email) {
            // Validate email format just in case
            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->warn("Skipping invalid email: {$email}");
                continue;
            }

            $user = User::where('email', $email)->first();

            if (! $user) {
                $this->warn("User not found: {$email} (Skipped - this command only links EXISTING users)");
                $notFoundCount++;
                continue;
            }

            DB::transaction(function () use ($user, $organizer, &$linkedCount, &$alreadyLinkedCount, $email) {
                $promoter = Promoter::firstOrCreate(
                    ['user_id' => $user->id],
                    ['enabled' => true, 'referral_code' => \Str::random(10)]
                );

                if (! $promoter->referral_code) {
                    $promoter->update(['referral_code' => \Str::random(10)]);
                }

                if ($promoter->organizers()->where('organizer_id', $organizer->id)->exists()) {
                    $this->line("Already linked: {$email}");
                    $alreadyLinkedCount++;
                } else {
                    $promoter->organizers()->attach($organizer->id, [
                        'enabled' => true,
                        'commission_type' => 'percentage',
                        'commission_value' => 10,
                    ]);
                    $this->info("Linked: {$email}");
                    $linkedCount++;
                }
            });
        }

        $this->table(
            ['Metric', 'Count'],
            [
                ['Linked', $linkedCount],
                ['Already Linked', $alreadyLinkedCount],
                ['User Not Found', $notFoundCount],
            ]
        );

        return Command::SUCCESS;
    }
}
