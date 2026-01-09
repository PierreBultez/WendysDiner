<?php

use Livewire\Volt\Volt;
use App\Models\Setting;

test('banner is visible when enabled', function () {
    Setting::updateOrCreate(['key' => 'banner_enabled'], ['value' => '1']);
    Setting::updateOrCreate(['key' => 'banner_text'], ['value' => 'Promo Spéciale']);

    $component = Volt::test('layout.banner');
    
    $component->assertSee('Promo Spéciale');
});

test('banner is hidden when disabled', function () {
    Setting::updateOrCreate(['key' => 'banner_enabled'], ['value' => '0']);
    Setting::updateOrCreate(['key' => 'banner_text'], ['value' => 'Promo Spéciale']);

    $component = Volt::test('layout.banner');
    
    $component->assertDontSee('Promo Spéciale');
});
