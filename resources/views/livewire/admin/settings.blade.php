<?php
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\Setting;

new #[Layout('components.layouts.admin')] #[Title('Paramètres - Admin')] class extends Component {
    public bool $payment_cb_enabled = true;
    public bool $banner_enabled = false;
    public string $banner_text = '';

    public function mount()
    {
        $this->payment_cb_enabled = (bool) Setting::where('key', 'payment_cb_enabled')->value('value') ?? true;
        $this->banner_enabled = (bool) Setting::where('key', 'banner_enabled')->value('value') ?? false;
        $this->banner_text = Setting::where('key', 'banner_text')->value('value') ?? '';
    }

    public function updatedPaymentCbEnabled($value)
    {
        Setting::updateOrCreate(['key' => 'payment_cb_enabled'], ['value' => $value ? 1 : 0]);
    }

    public function updatedBannerEnabled($value)
    {
        Setting::updateOrCreate(['key' => 'banner_enabled'], ['value' => $value ? 1 : 0]);
    }

    public function updatedBannerText($value)
    {
        Setting::updateOrCreate(['key' => 'banner_text'], ['value' => $value]);
    }
};
?>

<div>
    <h1 class="text-3xl font-bold text-accent-1 mb-8">Paramètres de la boutique</h1>

    <div class="space-y-6">
        <div class="bg-white dark:bg-zinc-800 p-6 rounded-lg shadow-sm">
            <h2 class="text-xl font-bold text-primary-text mb-4">Paiements</h2>
            
            <div class="space-y-4">
                 <flux:switch 
                    wire:model.live="payment_cb_enabled" 
                    label="Activer les paiements par Carte Bancaire" 
                    description="Désactivez cette option pour masquer/désactiver les paiements par carte (en ligne et sur place)."
                />
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-800 p-6 rounded-lg shadow-sm">
            <h2 class="text-xl font-bold text-primary-text mb-4">Bandeau d'information</h2>
            
            <div class="space-y-4">
                 <flux:switch 
                    wire:model.live="banner_enabled" 
                    label="Afficher le bandeau d'information" 
                    description="Active un bandeau visible tout en haut du site pour les annonces importantes."
                />

                @if($banner_enabled)
                    <flux:input 
                        wire:model.blur="banner_text" 
                        label="Texte du bandeau" 
                        placeholder="Ex: Fermeture exceptionnelle ce mardi..."
                    />
                @endif
            </div>
        </div>
    </div>
</div>
