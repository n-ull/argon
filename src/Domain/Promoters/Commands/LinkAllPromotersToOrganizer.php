<?php

namespace Domain\Promoters\Commands;

use Domain\OrganizerManagement\Models\Organizer;
use Domain\Promoters\Models\Promoter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class LinkAllPromotersToOrganizer extends Command
{
    protected $signature = 'promoters:link-all 
                            {organizer : The ID of the organizer}';

    protected $description = 'Link ALL existing promoters to the specified organizer';

    public function handle()
    {
        $organizerId = $this->argument('organizer');
        $organizer = Organizer::find($organizerId);

        if (! $organizer) {
            $this->error("Organizer with ID {$organizerId} not found.");
            return Command::FAILURE;
        }

        if (! $this->confirm("Are you sure you want to link ALL existing promoters to '{$organizer->name}'?")) {
            return Command::SUCCESS;
        }

        $promoters = Promoter::all();
        $totalPromoters = $promoters->count();

        $this->info("Found {$totalPromoters} promoters. Processing...");

        $this->output->progressStart($totalPromoters);

        $linkedCount = 0;
        $alreadyLinkedCount = 0;

        foreach ($promoters as $promoter) {
            DB::transaction(function () use ($promoter, $organizer, &$linkedCount, &$alreadyLinkedCount) {
                if ($promoter->organizers()->where('organizer_id', $organizer->id)->exists()) {
                    $alreadyLinkedCount++;
                } else {
                    $promoter->organizers()->attach($organizer->id, [
                        'enabled' => true,
                        'commission_type' => 'percentage', // Default
                        'commission_value' => 10, // Default
                    ]);
                    $linkedCount++;
                }
            });
            $this->output->progressAdvance();
        }

        $this->output->progressFinish();

        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Promoters Scanned', $totalPromoters],
                ['Newly Linked', $linkedCount],
                ['Already Linked', $alreadyLinkedCount],
            ]
        );

        return Command::SUCCESS;
    }
}
