<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;

new #[Title('Programme de Fidélité')]
#[Layout('components.layouts.app')]
class extends Component {
    public function with(): array
    {
        return [
            'points' => Auth::check() ? Auth::user()->loyalty_points : 0,
            'tiers' => config('wendys.loyalty'),
        ];
    }
}; ?>

<div class="min-h-screen py-12 bg-zinc-50 dark:bg-zinc-900">
    <div class="container mx-auto px-4 max-w-5xl">
        
        {{-- Header --}}
        <div class="text-center mb-16">
            <h1 class="text-5xl font-black text-transparent bg-clip-text bg-gradient-to-r from-accent-1 to-accent-2 mb-6 transform -rotate-2 inline-block">
                LE WENDY'S CLUB
            </h1>
            <p class="text-xl text-zinc-600 dark:text-zinc-300 max-w-2xl mx-auto leading-relaxed">
                Entre dans la légende ! Mange des burgers, gagne des points et débloque des récompenses <span class="font-bold text-accent-1">totalement gratuites</span>.
            </p>
        </div>

        {{-- How it works --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-20">
            <div class="bg-white dark:bg-zinc-800 p-8 rounded-2xl shadow-lg border-b-4 border-accent-1 transform hover:-translate-y-2 transition-transform">
                <div class="w-16 h-16 bg-accent-1/10 rounded-full flex items-center justify-center mb-6 mx-auto">
                    <flux:icon name="shopping-cart" class="size-8 text-accent-1" />
                </div>
                <h3 class="text-xl font-bold text-center mb-2 text-primary-text">1. Commande</h3>
                <p class="text-center text-zinc-500">Fais-toi plaisir avec tes burgers préférés en ligne ou sur place.</p>
            </div>
            <div class="bg-white dark:bg-zinc-800 p-8 rounded-2xl shadow-lg border-b-4 border-orange-500 transform hover:-translate-y-2 transition-transform">
                <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mb-6 mx-auto">
                    <flux:icon name="star" class="size-8 text-orange-500" />
                </div>
                <h3 class="text-xl font-bold text-center mb-2 text-primary-text">2. Gagne des points</h3>
                <p class="text-center text-zinc-500">Chaque euro dépensé te rapporte 1 point. C'est aussi simple que ça !</p>
            </div>
            <div class="bg-white dark:bg-zinc-800 p-8 rounded-2xl shadow-lg border-b-4 border-purple-500 transform hover:-translate-y-2 transition-transform">
                <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mb-6 mx-auto">
                    <flux:icon name="gift" class="size-8 text-purple-500" />
                </div>
                <h3 class="text-xl font-bold text-center mb-2 text-primary-text">3. Régale-toi</h3>
                <p class="text-center text-zinc-500">Utilise tes points pour obtenir des récompenses exclusives.</p>
            </div>
        </div>

        {{-- Tiers Display --}}
        <div class="relative">
            {{-- Connector Line --}}
            <div class="hidden md:block absolute top-1/2 left-0 w-full h-2 bg-zinc-200 dark:bg-zinc-700 -translate-y-1/2 rounded-full z-0"></div>

            <div class="grid grid-cols-1 md:grid-cols-5 gap-6 relative z-10">
                @foreach($tiers as $level => $tier)
                    <div class="group">
                        <div class="bg-white dark:bg-zinc-800 p-6 rounded-2xl shadow-md border-2 border-zinc-100 dark:border-zinc-700 hover:border-accent-1 dark:hover:border-accent-1 transition-all h-full flex flex-col items-center text-center">
                            
                            {{-- Badge --}}
                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-zinc-800 to-black text-white flex items-center justify-center font-black text-lg mb-4 shadow-lg group-hover:scale-110 transition-transform">
                                {{ $tier['points'] }}
                            </div>

                            <h4 class="font-black text-lg text-accent-1 mb-1 uppercase tracking-wide">{{ $tier['name'] }}</h4>
                            <p class="text-xs font-bold text-zinc-400 uppercase mb-4">Niveau {{ $level }}</p>
                            
                            <div class="mt-auto">
                                <p class="text-sm font-medium text-primary-text mb-2">Ta récompense :</p>
                                <div class="inline-block px-3 py-1 bg-accent-1/10 text-accent-1 rounded-full text-sm font-bold">
                                    {{ $tier['reward'] }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- CTA --}}
        <div class="mt-20 text-center">
            @auth
                <div class="bg-zinc-900 text-white p-8 rounded-3xl inline-block shadow-2xl max-w-2xl">
                    <p class="text-2xl font-bold mb-2">Tu as actuellement <span class="text-accent-1">{{ $points }} points</span> !</p>
                    <p class="text-zinc-400 mb-6">Continue comme ça, la prochaine récompense n'est pas loin.</p>
                    <flux:button href="{{ route('menu') }}" variant="primary" class="w-full sm:w-auto">Commander maintenant</flux:button>
                </div>
            @else
                <div class="bg-zinc-900 text-white p-8 rounded-3xl inline-block shadow-2xl max-w-2xl">
                    <p class="text-2xl font-bold mb-2">Rejoins le club dès maintenant !</p>
                    <p class="text-zinc-400 mb-6">Crée ton compte en 2 minutes et commence à cumuler des points dès ta première commande.</p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <flux:button href="{{ route('register') }}" variant="primary">Créer un compte</flux:button>
                        <flux:button href="{{ route('login') }}" variant="ghost" class="text-white hover:text-white border-zinc-700 hover:bg-zinc-800">Se connecter</flux:button>
                    </div>
                </div>
            @endauth
        </div>

    </div>
</div>
