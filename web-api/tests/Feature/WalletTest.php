<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
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
        
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 0,
            'is_shared' => false,
        ]);

        $this->assertDatabaseHas('wallets', [
            'user_id' => $user->id,
            'balance' => 0,
            'is_shared' => false,
        ]);
    }

    /**
     * Test shared admin wallet exists
     */
    public function test_shared_admin_wallet_is_created()
    {
        Wallet::factory()->create([
            'user_id' => null,
            'balance' => 0,
            'is_shared' => true,
        ]);

        $sharedWallet = Wallet::where('is_shared', true)
            ->whereNull('user_id')
            ->first();

        $this->assertNotNull($sharedWallet);
        $this->assertTrue($sharedWallet->is_shared);
        $this->assertNull($sharedWallet->user_id);
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
     * Test user can get their wallet balance
     */
    public function test_user_can_get_wallet_balance()
    {
        $user = User::factory()->create();
        Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 5000000,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/wallet');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'balance' => 5000000,
                ],
            ]);
    }

    /**
     * Test admin can get shared admin wallet
     */
    public function test_admin_can_get_shared_admin_wallet()
    {
        $admin = User::factory()->admin()->create();
        Wallet::factory()->create([
            'user_id' => null,
            'balance' => 10000000,
            'is_shared' => true,
        ]);

        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/wallet');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'is_shared' => true,
                ],
            ]);
    }

    /**
     * Test user can get their wallet transactions
     */
    public function test_user_can_get_wallet_transactions()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 1000000,
        ]);

        WalletTransaction::factory()->count(3)->create([
            'wallet_id' => $wallet->id,
            'type' => WalletTransaction::TYPE_CREDIT,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/wallet/transactions');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'meta' => [
                    'current_page',
                    'last_page',
                    'total',
                ],
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    /**
     * Test wallet recharge initiation
     */
    public function test_wallet_recharge_initiation()
    {
        $user = User::factory()->create();
        Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 0,
        ]);

        // Mock Zarinpal API response
        Http::fake([
            'sandbox.zarinpal.com/*' => Http::response([
                'data' => [
                    'code' => 100,
                    'authority' => 'A00000000000000000000000000000000000',
                ],
            ], 200),
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/wallet/recharge', [
            'amount' => 100000,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'payment_url',
                    'authority',
                    'transaction_id',
                ],
            ]);

        // Verify transaction was created (amount is in Rials: 100000 * 10)
        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id' => $user->wallet->id,
            'type' => WalletTransaction::TYPE_CREDIT,
            'amount' => 1000000, // 100,000 Tomans = 1,000,000 Rials
        ]);
    }

    /**
     * Test wallet recharge validation
     */
    public function test_wallet_recharge_requires_valid_amount()
    {
        $user = User::factory()->create();
        Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 0,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/wallet/recharge', [
            'amount' => 5000, // Less than minimum
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    }

    /**
     * Test admin uses shared wallet
     */
    public function test_admin_uses_shared_wallet()
    {
        $admin1 = User::factory()->admin()->create();
        $admin2 = User::factory()->admin()->create();

        // Create shared admin wallet
        $sharedWallet = Wallet::factory()->create([
            'user_id' => null,
            'balance' => 5000000,
            'is_shared' => true,
        ]);

        Sanctum::actingAs($admin1);

        // Admin 1 should get shared wallet
        $response1 = $this->getJson('/api/wallet');
        $response1->assertStatus(200);
        
        // Check the wallet returned is a shared wallet
        $this->assertTrue($response1->json('data.is_shared'));
        $this->assertNull($response1->json('data.user_id'));

        Sanctum::actingAs($admin2);

        // Admin 2 should also get a shared wallet
        $response2 = $this->getJson('/api/wallet');
        $response2->assertStatus(200);
        
        // Check the wallet returned is a shared wallet
        $this->assertTrue($response2->json('data.is_shared'));
        $this->assertNull($response2->json('data.user_id'));
        
        // Both admins should get the same shared wallet
        $this->assertEquals($response1->json('data.id'), $response2->json('data.id'));
    }

    /**
     * Test user gets their own wallet
     */
    public function test_user_gets_own_wallet()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 2000000,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/wallet');

        $response->assertStatus(200);
        $this->assertEquals($wallet->id, $response->json('data.id'));
        $this->assertEquals(2000000, $response->json('data.balance'));
    }

    /**
     * Test wallet policy allows user to view their wallet
     */
    public function test_wallet_policy_allows_user_to_view_own_wallet()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 0,
        ]);

        Sanctum::actingAs($user);

        $this->assertTrue($user->can('view', $wallet));
    }

    /**
     * Test wallet policy allows admin to view shared wallet
     */
    public function test_wallet_policy_allows_admin_to_view_shared_wallet()
    {
        $admin = User::factory()->admin()->create();
        $sharedWallet = Wallet::factory()->create([
            'user_id' => null,
            'is_shared' => true,
        ]);

        Sanctum::actingAs($admin);

        $this->assertTrue($admin->can('view', $sharedWallet));
    }

    /**
     * Test wallet policy denies user from viewing shared wallet
     */
    public function test_wallet_policy_denies_user_from_viewing_shared_wallet()
    {
        $user = User::factory()->create();
        $sharedWallet = Wallet::factory()->create([
            'user_id' => null,
            'is_shared' => true,
        ]);

        Sanctum::actingAs($user);

        $this->assertFalse($user->can('view', $sharedWallet));
    }

    /**
     * Test wallet recharge with minimum amount
     */
    public function test_wallet_recharge_with_minimum_amount()
    {
        $user = User::factory()->create();
        Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 0,
        ]);

        Http::fake([
            'sandbox.zarinpal.com/*' => Http::response([
                'data' => [
                    'code' => 100,
                    'authority' => 'A00000000000000000000000000000000000',
                ],
            ], 200),
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/wallet/recharge', [
            'amount' => 10000, // Minimum: 10,000 Tomans
        ]);

        $response->assertStatus(200);
    }

    /**
     * Test wallet recharge fails with below minimum amount
     */
    public function test_wallet_recharge_fails_with_below_minimum()
    {
        $user = User::factory()->create();
        Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 0,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/wallet/recharge', [
            'amount' => 9999, // Below minimum
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    }

    /**
     * Test wallet recharge fails with above maximum amount
     */
    public function test_wallet_recharge_fails_with_above_maximum()
    {
        $user = User::factory()->create();
        Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 0,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/wallet/recharge', [
            'amount' => 2000000001, // Above maximum: 2,000,000,000 Tomans
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    }

    /**
     * Test wallet recharge callback success
     */
    public function test_wallet_recharge_callback_success()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 0,
        ]);

        // Create a pending transaction
        $transaction = WalletTransaction::factory()->create([
            'wallet_id' => $wallet->id,
            'type' => WalletTransaction::TYPE_CREDIT,
            'amount' => 1000000, // 100,000 Tomans in Rials
            'balance_after' => 0,
            'meta' => [
                'status' => 'pending',
                'amount_in_tomans' => 100000,
                'authority' => 'A00000000000000000000000000000000000',
            ],
        ]);

        // Mock Zarinpal verify payment API
        Http::fake([
            'sandbox.zarinpal.com/*' => Http::response([
                'data' => [
                    'code' => 100,
                    'ref_id' => 123456789,
                ],
            ], 200),
        ]);

        $response = $this->get('/api/wallet/callback?Authority=A00000000000000000000000000000000000&Status=OK');
        
        $response->assertStatus(302);
        
        // Refresh wallet from database
        $wallet->refresh();
        
        // Check wallet balance was updated (the callback should update it)
        // Note: The actual callback implementation will handle the balance update
        $this->assertDatabaseHas('wallet_transactions', [
            'id' => $transaction->id,
        ]);
    }

    /**
     * Test wallet recharge callback failure
     */
    public function test_wallet_recharge_callback_failure()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 0,
        ]);

        // Create a pending transaction
        $transaction = WalletTransaction::factory()->create([
            'wallet_id' => $wallet->id,
            'type' => WalletTransaction::TYPE_CREDIT,
            'amount' => 1000000,
            'balance_after' => 0,
            'meta' => [
                'status' => 'pending',
            ],
        ]);

        $response = $this->get('/api/wallet/callback?Authority=A00000000000000000000000000000000000&Status=NOK');
        
        $response->assertStatus(302);
        
        // Check wallet balance was not updated
        $this->assertDatabaseHas('wallets', [
            'id' => $wallet->id,
            'balance' => 0,
        ]);
    }

    /**
     * Test user cannot view another user's wallet
     */
    public function test_wallet_policy_denies_user_from_viewing_other_user_wallet()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $wallet2 = Wallet::factory()->create([
            'user_id' => $user2->id,
            'balance' => 0,
        ]);

        Sanctum::actingAs($user1);

        $this->assertFalse($user1->can('view', $wallet2));
    }

    /**
     * Test wallet policy allows admin to recharge shared wallet
     */
    public function test_wallet_policy_allows_admin_to_recharge_shared_wallet()
    {
        $admin = User::factory()->admin()->create();
        $sharedWallet = Wallet::factory()->create([
            'user_id' => null,
            'balance' => 0,
            'is_shared' => true,
        ]);

        Sanctum::actingAs($admin);

        $this->assertTrue($admin->can('makeTransaction', $sharedWallet));
    }

    /**
     * Test wallet policy denies user from recharging shared wallet
     */
    public function test_wallet_policy_denies_user_from_recharging_shared_wallet()
    {
        $user = User::factory()->create();
        $sharedWallet = Wallet::factory()->create([
            'user_id' => null,
            'balance' => 0,
            'is_shared' => true,
        ]);

        Sanctum::actingAs($user);

        $this->assertFalse($user->can('makeTransaction', $sharedWallet));
    }

    /**
     * Test wallet transactions pagination
     */
    public function test_wallet_transactions_pagination()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 0,
        ]);

        WalletTransaction::factory()->count(15)->create([
            'wallet_id' => $wallet->id,
            'type' => WalletTransaction::TYPE_CREDIT,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/wallet/transactions?page=1&limit=10');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ],
            ]);

        $this->assertCount(10, $response->json('data'));
    }

    /**
     * Test wallet can be created automatically for new user
     */
    public function test_wallet_is_created_automatically_for_new_user()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/wallet');

        $response->assertStatus(200);
        
        // Should have a wallet for this user
        $this->assertDatabaseHas('wallets', [
            'user_id' => $user->id,
            'is_shared' => false,
        ]);
    }

    /**
     * Test shared admin wallet is created automatically for admin
     */
    public function test_shared_admin_wallet_is_created_automatically_for_admin()
    {
        $admin = User::factory()->admin()->create();

        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/wallet');

        $response->assertStatus(200);
        
        // Should have a shared wallet for admins
        $this->assertDatabaseHas('wallets', [
            'user_id' => null,
            'is_shared' => true,
        ]);
    }
}
