<?php

namespace App\Console\Commands;

use App\Models\User;
use Domain\Ticketing\Models\Ticket;
use Illuminate\Console\Command;

class DeleteTicketsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:tickets {user_id} {product_id?} {--except=* : Product IDs to exclude from deletion} {--except-ticket=* : Specific Ticket IDs to exclude}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bulk delete tickets for a specific user with optional product filter and exceptions.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id');
        $productIdOrStar = $this->argument('product_id') ?: '*';
        $exceptProducts = $this->option('except');
        $exceptTickets = $this->option('except-ticket');

        $user = User::find($userId);

        if (!$user) {
            $this->error("User with ID {$userId} not found.");
            return 1;
        }

        $query = Ticket::where('user_id', $userId);

        // Filter by product_id if specified (and not '*')
        if ($productIdOrStar !== '*') {
            $query->where('product_id', $productIdOrStar);
        }


        // Apply product exceptions
        if (!empty($exceptProducts)) {
            $query->whereNotIn('product_id', $exceptProducts);
        }

        // Apply specific ticket exceptions
        if (!empty($exceptTickets)) {
            $query->whereNotIn('id', $exceptTickets);
        }

        $count = $query->count();

        if ($count === 0) {
            $this->info("No tickets found matching the criteria for user {$user->name} (ID: {$userId}).");
            return 0;
        }

        $this->warn("Found {$count} tickets to delete.");

        if (!$this->confirm("Are you sure you want to delete these tickets?")) {
            $this->info("Operation cancelled.");
            return 0;
        }

        // Using delete() on the query will perform a bulk soft delete if the model uses SoftDeletes
        $deleted = $query->delete();

        $this->info("Successfully deleted {$deleted} tickets for user {$user->name}.");

        return 0;
    }
}
