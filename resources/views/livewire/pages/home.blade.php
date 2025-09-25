<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Title;
use App\Models\Product;
use function Livewire\Volt\with;

new #[Title("Accueil - Wendy's Diner")] class extends Component
{
    /**
     * Provide the featured products to the view.
     * This method fetches products where 'featured' is true.
     */
    public function with(): array
    {
        return [
            'featuredProducts' => Product::where('featured', true)
                ->orderBy('name')
                ->take(5) // We only want to show a maximum of 5
                ->get(),
        ];
    }
}; ?>

<div>
    <!-- =================================================================== -->
    <!-- HERO SECTION                                                        -->
    <!-- =================================================================== -->
    <section class="relative min-h-[90vh] flex items-center justify-center text-center bg-zinc-800 text-white overflow-hidden">
        {{-- Background Image --}}
        <div class="absolute inset-0 z-0">
            <img src="/images/placeholders/diner-hero.jpg" alt="Intérieur du restaurant Wendy's Diner" class="w-full h-full object-cover opacity-40">
        </div>

        {{-- Content --}}
        <div class="relative z-10 p-4">
            <h1 class="text-6xl md:text-8xl font-bold text-white drop-shadow-lg" style="-webkit-text-stroke: 2px var(--color-accent-1);">
                Le Goût Authentique
            </h1>
            <p class="mt-4 max-w-2xl mx-auto text-lg md:text-xl text-white/90">
                Plongez dans l'ambiance des années 50 et redécouvrez les saveurs uniques de l'Amérique.
            </p>
            <div class="mt-8">
                {{-- CORRECTIF : Remplacement de size="lg" par des classes Tailwind --}}
                <flux:button
                    variant="primary"
                    href="/carte"
                    wire:navigate
                    class="px-8 py-3 text-lg font-bold"
                >
                    Découvrir la carte
                </flux:button>
            </div>
        </div>
    </section>

    <!-- =================================================================== -->
    <!-- "NOS INCONTOURNABLES" SECTION (NOW DYNAMIC & FLEXIBLE)              -->
    <!-- =================================================================== -->
    <section class="py-16 md:py-24 bg-background">
        <div class="container mx-auto px-4">
            <h2 class="text-4xl md:text-5xl font-bold text-center text-primary-text">Nos Incontournables</h2>
            <p class="text-center mt-2 text-primary-text/70">Les favoris de nos clients, préparés avec amour.</p>

            @if($featuredProducts->isNotEmpty())
                {{-- CORRECTIF : Remplacement de Grid par Flexbox pour un centrage dynamique --}}
                <div class="mt-12 flex flex-nowrapwrap justify-center gap-8">
                    @foreach($featuredProducts as $product)
                        {{-- On donne une largeur maximale à chaque carte pour un affichage cohérent --}}
                        <div class="w-full max-w-sm flex flex-col border border-primary-text/10 rounded-lg overflow-hidden shadow-lg transition-transform hover:scale-105">
                            {{-- L'image ne bouge pas --}}
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-56 object-cover">

                            {{-- CORRECTIF : Ce conteneur devient une colonne flexible qui grandit --}}
                            <div class="p-6 flex flex-col flex-grow">
                                <h3 class="text-2xl font-bold text-accent-1">{{ $product->name }}</h3>
                                <p class="mt-2 text-primary-text/80 text-sm">{{ $product->description }}</p>

                                {{-- CORRECTIF : Ce div pousse le prix en bas --}}
                                <div class="mt-auto pt-4">
                                    <div class="text-xl font-bold text-accent-2">
                                        {{ number_format($product->price, 2, ',', ' ') }} €
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                {{-- ... (inchangé) ... --}}
            @endif
        </div>
    </section>

    <!-- =================================================================== -->
    <!-- "L'EXPÉRIENCE WENDY'S" SECTION                                      -->
    <!-- =================================================================== -->
    <section class="py-16 md:py-24 bg-primary-text text-background">
        <div class="container mx-auto px-4 grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
            {{-- Image Column --}}
            <div>
                <img src="/images/placeholders/diner-interior.jpg" alt="Ambiance vintage chez Wendy's Diner" class="rounded-lg shadow-2xl">
            </div>

            {{-- Text Column --}}
            <div>
                <h2 class="text-4xl md:text-5xl font-bold text-accent-2">Plus qu'un repas, une expérience</h2>
                <p class="mt-4 text-background/80">
                    Chez Wendy's, chaque détail compte. Des banquettes en cuir rouge à notre jukebox d'époque, nous avons recréé l'atmosphère authentique des diners américains pour vous faire voyager dans le temps.
                </p>
                <p class="mt-4 text-background/80">
                    Venez pour nos burgers, restez pour l'ambiance !
                </p>
                <div class="mt-8">
                    <flux:button variant="ghost" href="/histoire" wire:navigate class="!text-accent-2 !border-accent-2 hover:!bg-accent-2 hover:!text-primary-text">
                        Notre histoire
                    </flux:button>
                </div>
            </div>
        </div>
    </section>

    <!-- =================================================================== -->
    <!-- GOOGLE REVIEWS PLACEHOLDER                                          -->
    <!-- =================================================================== -->
    <section class="py-16 md:py-24 bg-background">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-4xl md:text-5xl font-bold text-primary-text">Ce que nos clients disent</h2>
            <div class="mt-12 max-w-2xl mx-auto p-8 border-2 border-dashed border-accent-2/50 rounded-lg">
                <p class="text-primary-text/70">
                    L'intégration des avis Google arrive bientôt !
                </p>
                <p class="mt-2 text-sm text-primary-text/50">
                    (Phase 7.2 du plan de développement)
                </p>
            </div>
        </div>
    </section>
</div>
