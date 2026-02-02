<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateLegacyUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate-legacy-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $legacyUsers = DB::connection('legacy')->table('users')->whereNotNull('email')->get();

        foreach ($legacyUsers as $legacyUser) {
            if (User::where('email', $legacyUser->email)->exists()) {
                continue;
            }

            User::create([
                'name' => $legacyUser->name ?? 'No Name',
                'email' => $legacyUser->email,
                'password' => $legacyUser->password, // same hash
                'created_at' => $legacyUser->created_at,
                'updated_at' => $legacyUser->updated_at,
            ]);
        }
    }
}
