<?php

namespace App\Jobs;

use App\Services\MercadoPagoService;
use Domain\OrganizerManagement\Models\MercadoPagoAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class RefreshMercadoPagoTokens implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Get accounts that are expiring within the next 30 days to be safe, 
        // or just iterate all since traffic is likely low. 
        // MP tokens last 180 days. Let's look for accounts updated > 100 days ago.

        $accounts = MercadoPagoAccount::where('updated_at', '<', Carbon::now()->subDays(100))->get();
        // Alternatively, if expires_in is stored, we could use that.
        // But "updated_at" is when we last got the token.

        // Actually, let's just refresh any account that hasn't been updated in 30 days to keep them fresh.
        $accounts = MercadoPagoAccount::where('updated_at', '<', Carbon::now()->subDays(30))->get();

        foreach ($accounts as $account) {
            try {
                MercadoPagoService::refreshToken($account);
            } catch (\Exception $e) {
                // Continue to next account
                continue;
            }
        }
    }
}
