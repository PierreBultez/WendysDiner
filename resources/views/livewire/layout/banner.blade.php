<?php

use Livewire\Volt\Component;
use App\Models\Setting;

new class extends Component {
    public bool $enabled = false;
    public string $text = '';

    public function mount()
    {
        $this->enabled = (bool) Setting::where('key', 'banner_enabled')->value('value');
        $this->text = Setting::where('key', 'banner_text')->value('value') ?? '';
    }
}; ?>

<div>
    @if($enabled && !empty($text))
        <div class="bg-accent-1 text-white py-2 px-4 text-center text-sm font-medium relative z-50">
            <div class="container mx-auto flex items-center justify-center gap-2">
                <flux:icon name="megaphone" class="size-4" />
                <span>{{ $text }}</span>
            </div>
        </div>
    @endif
</div>