<?php

namespace App\Console\Commands;

use App\Mail\OrderCompleted;
use Domain\EventManagement\Models\Event;
use Domain\Ordering\Enums\OrderStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class ResendOrderCompletedMailables extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:resend-order-emails 
                            {event_id : The ID of the event} 
                            {--force : Force the operation to run without confirmation}
                            {--dry-run : Only simulate the email sending}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resend all the completed order emails for an event';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $eventId = $this->argument('event_id');
        $isDryRun = $this->option('dry-run');

        // Validate event exists
        $event = Event::find($eventId);
        if (! $event) {
            $this->components->error("❌ Event with ID {$eventId} not found.");

            return 1;
        }

        $orders = $event->orders()->where('status', OrderStatus::COMPLETED)->get();

        if ($orders->isEmpty()) {
            $this->components->info("No completed orders found for event: {$event->name}");

            return 0;
        }

        $count = $orders->count();

        if (! $this->option('force') && ! $this->confirm("Do you really want to resend {$count} emails for event: {$event->name}?", true)) {
            $this->components->warn('Operation cancelled.');

            return 0;
        }

        $this->components->info(($isDryRun ? '[DRY RUN] ' : '') . "Resending {$count} order completed emails...");

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $successCount = 0;
        $failCount = 0;

        foreach ($orders as $order) {
            try {
                if (! $isDryRun) {
                    Mail::to($order->client->email)->send(new OrderCompleted($order));
                }
                $successCount++;
            } catch (\Exception $e) {
                $failCount++;
                $this->newLine();
                $this->components->error("Failed to send email to {$order->client->email}: {$e->getMessage()}");
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        if ($failCount > 0) {
            $this->components->warn("Completed with errors: {$successCount} sent, {$failCount} failed.");
        } else {
            $this->components->info("Successfully processed {$successCount} emails.");
        }

        return 0;
    }
}
