<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Str;
use Tests\CreatesApplication;

class AdminAccountingFlowTest extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        config()->set('cache.default', 'array');
        config()->set('session.driver', 'array');

        $this->artisan('migrate:fresh');
        $this->artisan('db:seed');
    }

    public function test_locked_agency_context_is_preserved_in_create_and_store_flow(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin-flow@example.com',
            'password' => 'secret123',
            'level' => User::LEVEL_ADMIN,
            'is_active' => true,
        ]);

        $agencyUser = User::create([
            'name' => 'Agency User',
            'email' => 'agency-flow@example.com',
            'password' => 'secret123',
            'level' => User::LEVEL_AGENCY,
            'is_active' => true,
        ]);

        $this->actingAs($admin);

        $indexResponse = $this->get(route('admin.accounting.index', [
            'locked_agency_id' => $agencyUser->id,
        ]));

        $indexResponse
            ->assertOk()
            ->assertSee('Genel Muhasebeye Dön')
            ->assertSee('locked_agency_id=' . $agencyUser->id, false);

        $createResponse = $this->get(route('admin.accounting.create', [
            'locked_agency_id' => $agencyUser->id,
        ]));

        $createResponse
            ->assertOk()
            ->assertSee('name="locked_agency_id"', false)
            ->assertSee('value="' . $agencyUser->id . '"', false)
            ->assertSee('locked_agency_id=' . $agencyUser->id, false);

        $storeResponse = $this->post(route('admin.accounting.store'), [
            'type' => 'income',
            'title' => 'Test Locked Agency Entry',
            'amount' => 100,
            'currency' => 'TRY',
            'transaction_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'status' => 'paid',
            'notes' => 'Test note',
            'locked_agency_id' => $agencyUser->id,
        ]);

        $storeResponse->assertRedirect(route('admin.accounting.index', [
            'locked_agency_id' => $agencyUser->id,
        ]));
    }

    public function test_locked_agency_filter_matches_ticket_through_split_accounting_columns(): void
    {
        $admin = User::create([
            'name' => 'Admin Filter',
            'email' => 'admin-filter@example.com',
            'password' => 'secret123',
            'level' => User::LEVEL_ADMIN,
            'is_active' => true,
        ]);

        $agencyA = User::create([
            'name' => 'Agency A',
            'email' => 'agency-a@example.com',
            'password' => 'secret123',
            'level' => User::LEVEL_AGENCY,
            'is_active' => true,
        ]);

        $agencyB = User::create([
            'name' => 'Agency B',
            'email' => 'agency-b@example.com',
            'password' => 'secret123',
            'level' => User::LEVEL_AGENCY,
            'is_active' => true,
        ]);

        $restTx = Transaction::create([
            'type' => 'income',
            'title' => 'Split Rest Test Transaction',
            'amount' => 250,
            'currency' => 'TRY',
            'transaction_date' => now()->toDateString(),
            'payment_method' => 'rest-adjustment',
            'status' => 'paid',
            'created_by' => $admin->id,
        ]);

        Ticket::withoutEvents(function () use ($agencyA, $restTx): void {
            Ticket::create([
                'entry_date' => now()->toDateString(),
                'entry_time' => now()->format('H:i:s'),
                'voucher_no' => 'TEST-' . Str::upper(Str::random(6)),
                'tracking_no' => 'TRK-' . Str::upper(Str::random(10)),
                'tour_date' => now()->addDay()->toDateString(),
                'pickup_time' => now()->format('H:i:s'),
                'tour_country' => 'TR',
                'tour_region' => 'Antalya',
                'tour_name' => 'Test Tour',
                'sales_agency' => 'Agency A',
                'customer_name' => 'Test Customer',
                'customer_phone' => '5550000000',
                'pickup_location' => 'Hotel',
                'total_price' => 500,
                'deposit' => 150,
                'rest' => 350,
                'currency' => 'EUR',
                'is_active' => true,
                'created_by_user_id' => $agencyA->id,
                'accounting_rest_transaction_id' => $restTx->id,
            ]);
        });

        $this->actingAs($admin);

        $forAgencyA = $this->get(route('admin.accounting.index', [
            'locked_agency_id' => $agencyA->id,
        ]));
        $forAgencyA->assertOk()->assertSee('Split Rest Test Transaction');

        $forAgencyB = $this->get(route('admin.accounting.index', [
            'locked_agency_id' => $agencyB->id,
        ]));
        $forAgencyB->assertOk()->assertDontSee('Split Rest Test Transaction');
    }
}

