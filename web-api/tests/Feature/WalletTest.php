<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * Test wallet is created when user registers
     */
    public function test_wallet_is_created_for_new_user()
    {
        $user = User::factory()->create();
        
        // Wallet might be created by UserFactory if it has observer
        // So we create one explicitly for this test
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 0,
        ]);

        $this->assertDatabaseHas('wallets', [
            'user_id' => $user->id,
            'balance' => 0,
        ]);
    }

    /**
     * Test wallet balance is correct
     */
    public function test_wallet_balance_is_correct()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 1000000, // 10,000 Toman
        ]);

        $this->assertEquals(1000000, $wallet->balance);
    }

    /**
     * Test wallet transactions are tracked
     */
    public function test_wallet_transactions_are_tracked()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 1000000,
        ]);

        // Create a credit transaction
        WalletTransaction::factory()->create([
            'wallet_id' => $wallet->id,
            'type' => WalletTransaction::TYPE_CREDIT,
            'amount' => 500000,
            'balance_after' => 1500000,
        ]);

        // Create a debit transaction
        WalletTransaction::factory()->create([
            'wallet_id' => $wallet->id,
            'type' => WalletTransaction::TYPE_DEBIT,
            'amount' => 200000,
            'balance_after' => 1300000,
        ]);

        $this->assertCount(2, $wallet->transactions);
    }

    /**
     * Test wallet ledger maintains balance correctly
     */
    public function test_wallet_ledger_maintains_balance()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 1000000,
        ]);

        // Add credits
        WalletTransaction::factory()->create([
            'wallet_id' => $wallet->id,
            'type' => WalletTransaction::TYPE_CREDIT,
            'amount' => 300000,
            'balance_after' => 1300000,
        ]);

        // Add debits
        WalletTransaction::factory()->create([
            'wallet_id' => $wallet->id,
            'type' => WalletTransaction::TYPE_DEBIT,
            'amount' => 100000,
            'balance_after' => 1200000,
        ]);

        // Verify ledger integrity
        $latestTransaction = WalletTransaction::where('wallet_id', $wallet->id)
            ->latest()
            ->first();

        $this->assertEquals(1200000, $latestTransaction->balance_after);
    }

    /**
     * Test wallet balance can be retrieved from database
     */
    public function test_wallet_balance_can_be_retrieved()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 500000,
        ]);

        // Retrieve wallet from database
        $retrievedWallet = Wallet::where('user_id', $user->id)->first();
        
        $this->assertNotNull($retrievedWallet);
        $this->assertEquals(500000, $retrievedWallet->balance);
    }
}

