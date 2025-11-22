<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

Volt::route('/', 'pages.home')->name('home');
Volt::route('/histoire', 'pages.story')->name('story');
Volt::route('/infos', 'pages.infos')->name('infos');
Volt::route('/carte', 'pages.menu')->name('menu');
Volt::route('/checkout', 'pages.checkout')->name('checkout');
Volt::route('/commande-confirmee', 'pages.success')->name('success');

// ADMIN DASHBOARD ROUTES
Route::middleware(['auth', 'verified'])->prefix('dashboard')->name('dashboard.')->group(function () {
    Volt::route('/', 'admin.dashboard')->name('index');
    Volt::route('/categories', 'admin.categories.index')->name('categories.index');
    Volt::route('/products', 'admin.products.index')->name('products.index');
    Volt::route('/pos', 'admin.pos.index')->name('pos');
    Volt::route('/orders', 'admin.orders.index')->name('orders.index');
});

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');

    Volt::route('settings/two-factor', 'settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});

require __DIR__.'/auth.php';
