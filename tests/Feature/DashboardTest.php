<?php

use App\Models\User;
use App\Models\Order;
use Carbon\Carbon;
use Livewire\Volt\Volt;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard.index'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create(['is_admin' => true]);
    $this->actingAs($user);

    $response = $this->get(route('dashboard.index'));
    $response->assertStatus(200);
});

test('dashboard chart data calculates correctly', function () {
    $user = User::factory()->create(['is_admin' => true]);
    $this->actingAs($user);

    // Clear orders
    Order::query()->delete();

    // Create orders
    // Order 1: Today 10:00 - 100€
    Order::forceCreate([
        'user_id' => $user->id,
        'total_amount' => 100,
        'status' => 'completed',
        'created_at' => Carbon::now()->setHour(10)->setMinute(0)->setSecond(0)
    ]);

    // Order 2: Same Month but earlier (Day 1 of month) - 50€
    // Ensure it's not today to differentiate daily vs monthly
    $startOfMonth = Carbon::now()->startOfMonth();
    if ($startOfMonth->isToday()) {
         // If today is 1st, use same day but different hour? 
         // Logic handles daily by hour.
         // Let's just rely on the 'daily' filter only picking up today.
         // And 'monthly' picking up both if they are in same month.
    } else {
        Order::forceCreate([
            'user_id' => $user->id,
            'total_amount' => 50,
            'status' => 'completed',
            'created_at' => $startOfMonth->setHour(12)
        ]);
    }

    // Order 3: Last Month - 200€ (Should be ignored in monthly view)
    Order::forceCreate([
        'user_id' => $user->id,
        'total_amount' => 200,
        'status' => 'completed',
        'created_at' => Carbon::now()->subMonth()->setHour(12)
    ]);
    
    // Order 4: Cancelled Today - 300€ (Should be ignored because of status)
    Order::forceCreate([
        'user_id' => $user->id,
        'total_amount' => 300,
        'status' => 'annulée',
        'created_at' => Carbon::now()->setHour(11)
    ]);

    // Test 1: Week View (Instead of Daily)
    // Assuming today is within the current week
    $component = Volt::test('admin.dashboard')
        ->set('period', 'week');
    
    $data = $component->get('stats');
    
    // Should include 100€ (Today) and 50€ (Start of Month) IF Start of Month is in current week.
    // To be safe, let's calculate what we expect.
    $startOfWeek = Carbon::now()->startOfWeek();
    $expectedWeek = 100.0;
    
    // Check if the "start of month" order falls in this week
    if (!$startOfMonth->isToday() && $startOfMonth->gte($startOfWeek)) {
        $expectedWeek += 50.0;
    }
    
    expect($data['total'])->toBe((float)$expectedWeek);
    
    // Test 2: Monthly View (renamed from 'monthly' to 'month' in logic, though value was 'monthly' in previous code, now it is 'month')
    $component->set('period', 'month');
    
    $dataMonthly = $component->get('stats');
    
    // Should include 100€ + 50€ = 150€ (if 50€ was created above)
    $expectedMonthly = $startOfMonth->isToday() ? 100.0 : 150.0;
    
    expect($dataMonthly['total'])->toBe((float)$expectedMonthly);

});