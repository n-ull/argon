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
                            {emails* : List of emails to link} 
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

        $emails = $this->argument('emails');
        $filePath = $this->option('file');

        if ($filePath) {
            if (! file_exists($filePath)) {
                $this->error("File not found: {$filePath}");
                return Command::FAILURE;
            }
            $fileEmails = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $emails = array_merge($emails, $fileEmails);
        }

        $emails = array_unique(array_filter(array_map('trim', $emails)));

        if (empty($emails)) {
            $this->warn("No emails provided.");
            return Command::SUCCESS;
        }

        $this->info("Processing ".count($emails)." emails for organizer: {$organizer->name}");

        $linkedCount = 0;
        $notFoundCount = 0;
        $alreadyLinkedCount = 0;

        foreach ($emails as $email) {
            $user = User::where('email', $email)->first();

            if (! $user) {
                $this->warn("User not found: {$email}");
                $notFoundCount++;
                continue;
            }

            DB::transaction(function () use ($user, $organizer, &$linkedCount, &$alreadyLinkedCount, $email) {
                $promoter = Promoter::firstOrCreate(
                    ['user_id' => $user->id],
                    ['enabled' => true, 'referral_code' => \Str::random(10)] // Ensure referral code is generated if creating
                );

                // Ensure referral code if it wasn't set (though firstOrCreate handles 'create', but standard promoter creation might have logic)
                if (! $promoter->referral_code) {
                    $promoter->update(['referral_code' => \Str::random(10)]);
                }

                if ($promoter->organizers()->where('organizer_id', $organizer->id)->exists()) {
                    $this->line("Already linked: {$email}");
                    $alreadyLinkedCount++;
                } else {
                    $promoter->organizers()->attach($organizer->id, [
                        'enabled' => true,
                        'commission_type' => 'percentage', // Default, maybe should be configurable?
                        'commission_value' => 10, // Default
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
