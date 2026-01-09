<?php

use App\Models\User;
use App\Models\Setting;
use Livewire\Volt\Volt;

test('admin can visit settings page', function () {
    $user = User::factory()->create(['is_admin' => true]);
    $this->actingAs($user);

    $response = $this->get(route('dashboard.settings'));
    $response->assertStatus(200);
});

test('non-admin cannot visit settings page', function () {
    $user = User::factory()->create(['is_admin' => false]);
    $this->actingAs($user);

    $response = $this->get(route('dashboard.settings'));
    $response->assertRedirect(route('my-orders'));
});

test('admin can toggle payment setting', function () {
    $user = User::factory()->create(['is_admin' => true]);
    $this->actingAs($user);

    $component = Volt::test('admin.settings')
        ->set('payment_cb_enabled', false);

    $this->assertDatabaseHas('settings', [
        'key' => 'payment_cb_enabled',
        'value' => '0',
    ]);

    $component->set('payment_cb_enabled', true);

    $this->assertDatabaseHas('settings', [
        'key' => 'payment_cb_enabled',
        'value' => '1',
    ]);
});
