<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Laravel\Sanctum\PersonalAccessToken;

class CleanupExpiredTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auth:cleanup-tokens {--days=30 : Number of days to keep tokens}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired and old personal access tokens';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');
        $cutoffDate = now()->subDays($days);

        $this->info("Cleaning up tokens older than {$days} days...");

        // Delete expired tokens
        $expiredCount = PersonalAccessToken::where('expires_at', '<', now())->delete();
        $this->info("Deleted {$expiredCount} expired tokens.");

        // Delete old tokens
        $oldCount = PersonalAccessToken::where('created_at', '<', $cutoffDate)->delete();
        $this->info("Deleted {$oldCount} old tokens.");

        // Clean up tokens for inactive users
        $inactiveUsers = User::where('is_active', false)->get();
        $inactiveTokenCount = 0;

        foreach ($inactiveUsers as $user) {
            $count = $user->tokens()->delete();
            $inactiveTokenCount += $count;
        }

        $this->info("Deleted {$inactiveTokenCount} tokens for inactive users.");

        $totalDeleted = $expiredCount + $oldCount + $inactiveTokenCount;
        $this->info("Total tokens deleted: {$totalDeleted}");

        return Command::SUCCESS;
    }
}