<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard.index'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create(['is_admin' => true]); // Ensure user is admin
    $this->actingAs($user);

    $response = $this->get(route('dashboard.index'));
    $response->assertStatus(200);
});