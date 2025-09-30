<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Title;
use App\Models\Product;
use App\Services\GooglePlacesService;
use function Livewire\Volt\with;

new #[Title("Wendy's Diner - Burgers & Frites Maison à Courthézon")] class extends Component
{
    /**
     * Provide the featured products to the view.
     * This method fetches products where 'featured' is true.
     */
    public function with(GooglePlacesService $googlePlacesService): array // <-- 2. Inject the service
    {
        return [
            'featuredProducts' => Product::where('featured', true)->orderBy('name')->take(4)->get(),
            'reviews' => $googlePlacesService->getReviews(), // <-- 3. Get the reviews
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
                Vrais Burgers, Vraies Frites
            </h1>
            <p class="mt-12 max-w-2xl mx-auto text-lg md:text-xl text-white/90">
                Le <strong>restaurant</strong> que vous attendiez à <strong>Courthézon</strong>. Wendy's Diner vous propose des <strong>hamburgers</strong> faits maison et des frites authentiques cuites au gras de bœuf, dans une ambiance <strong>diner</strong> simple et conviviale.
            </p>
            <div class="mt-16">
                <flux:button
                    variant="primary"
                    href="/carte"
                    wire:navigate
                    class="!px-12 py-8 text-xl font-bold"
                >
                    Découvrir la carte
                </flux:button>
            </div>
        </div>
    </section>

    <!-- =================================================================== -->
    <!-- "NOS INCONTOURNABLES" SECTION (REFACTORED)                          -->
    <!-- =================================================================== -->
    <section class="py-16 md:py-24 bg-background">
        <div class="container mx-auto px-4">
            {{-- On utilise maintenant notre composant <x-section-title> --}}
            <x-section-title
                title="Nos Incontournables"
                subtitle="Les favoris de nos clients, préparés avec amour."
            />

            @if($featuredProducts->isNotEmpty())
                <div class="mt-12 flex flex-wrap justify-center gap-8">
                    @foreach($featuredProducts as $product)
                        {{-- On utilise maintenant notre composant <x-card> --}}
                        <x-card class="max-w-xs overflow-hidden">
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-56 object-cover">
                            <div class="p-6 flex flex-col flex-grow">
                                <h3 class="text-2xl font-bold text-accent-1">{{ $product->name }}</h3>
                                <p class="mt-2 text-primary-text/80 text-sm">{{ $product->description }}</p>
                                <div class="mt-auto pt-4">
                                    <div class="text-xl font-bold text-accent-2">
                                        {{ number_format($product->price, 2, ',', ' ') }} €
                                    </div>
                                </div>
                            </div>
                        </x-card>
                    @endforeach
                </div>
            @else
                <div class="mt-12 text-center text-primary-text/60">
                    <p>Aucun produit mis en avant pour le moment. Revenez bientôt !</p>
                </div>
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

            {{-- On utilise notre composant <x-section-title> ici aussi, en alignant le texte à gauche --}}
            <div class="space-y-4">
                {{-- On utilise maintenant la prop 'titleClasses' pour changer la couleur --}}
                <x-section-title
                    class="!text-left"
                    title="Viande fraîche, frites maison, pains locaux"
                    titleClasses="text-accent-2"
                    style="-webkit-text-stroke: 0.1px white;"
                />

                <p class="text-background/80">
                    Notre promesse est simple : des ingrédients frais pour un goût authentique. C'est la base de chaque <strong class="text-accent-2">hamburger</strong> que nous servons dans notre <strong class="text-accent-2">restaurant</strong>. La viande de bœuf est sélectionnée avec soin, nos pains sont pétris de manière artisanale à <strong class="text-accent-2">Avignon</strong>, et surtout, nos frites sont fraîches, préparées chaque jour et cuites au gras de bœuf comme le veut la tradition du Nord.
                </p>
                <p class="text-background/80">
                    Wendy's Diner n'est pas juste un <strong class="text-accent-2">snack</strong> ou un <strong class="text-accent-2">fast-food</strong> de plus. C'est l'histoire d'une passion pour les authentiques <strong class="text-accent-2">diners</strong> américains et les vraies frites belges, réimaginée ici, au cœur du Vaucluse. Soutenu par une communauté incroyable, notre <strong class="text-accent-2">resto</strong> est un lieu où l'on vient pour la qualité à prix juste, et où l'on reste pour l'ambiance chaleureuse.</p>
                <p>Vous cherchez le meilleur <strong class="text-accent-2">burger</strong> de <strong class="text-accent-2">Courthézon</strong> ? Un <strong class="text-accent-2">restaurant</strong> où manger des <strong class="text-accent-2">frites</strong> maison près d'<strong class="text-accent-2">Orange</strong> ou <strong class="text-accent-2">Jonquières</strong> ? Ne cherchez plus. Wendy's Diner est votre nouvelle destination pour une expérience unique.
                </p>
                <div class="pt-4">
                    <flux:button variant="ghost" href="/histoire" wire:navigate class="!text-accent-2 !border-accent-2 border hover:!bg-accent-2 hover:!text-primary-text">
                        Découvrir notre histoire
                    </flux:button>
                </div>
            </div>
        </div>
    </section>

    <!-- =================================================================== -->
    <!-- GOOGLE REVIEWS SECTION (REFACTORED)                                 -->
    <!-- =================================================================== -->
    <section class="py-16 md:py-24 bg-background">
        <div class="container mx-auto px-4 text-center">
            <x-section-title
                title="Ce que nos clients disent"
            />

            <div class="mt-12 flex flex-wrap justify-center gap-8">
                @forelse($reviews as $review)
                    {{-- On réutilise le même composant <x-card> --}}
                    <x-card class="max-w-md p-6 text-left">
                        <div class="flex items-center mb-4">
                            <img src="{{ $review['profile_photo_url'] }}" alt="{{ $review['author_name'] }}" class="w-12 h-12 rounded-full mr-4">
                            <div>
                                <p class="font-bold text-primary-text">{{ $review['author_name'] }}</p>
                                <p class="text-sm text-zinc-500">{{ $review['relative_time_description'] }}</p>
                            </div>
                        </div>
                        <div class="flex items-center mb-4">
                            @for ($i = 0; $i < 5; $i++)
                                <svg class="w-5 h-5 {{ $i < $review['rating'] ? 'text-accent-2' : 'text-zinc-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            @endfor
                        </div>
                        <p class="text-primary-text/80 text-sm italic flex-grow">"{{ Illuminate\Support\Str::limit($review['text'], 200) }}"</p>
                    </x-card>
                @empty
                    <div class="text-center text-primary-text/60">
                        <p>Impossible de charger les avis pour le moment.</p>
                    </div>
                @endforelse

                {{-- On utilise la variante "cta" de notre composant <x-card> --}}
                <x-card variant="cta" class="max-w-md p-6">
                    <div class="flex items-center">
                        {{-- Google 'G' Logo SVG --}}
                        <svg class="w-12 h-12" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303c-1.649 4.657-6.08 8-11.303 8c-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4C12.955 4 4 12.955 4 24s8.955 20 20 20s20-8.955 20-20c0-1.341-.138-2.65-.389-3.917z"></path><path fill="#FF3D00" d="M6.306 14.691l6.571 4.819C14.655 15.108 18.961 12 24 12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4C16.318 4 9.656 8.337 6.306 14.691z"></path><path fill="#4CAF50" d="M24 44c5.166 0 9.86-1.977 13.409-5.192l-6.19-5.238A11.91 11.91 0 0 1 24 36c-5.222 0-9.618-3.226-11.283-7.581l-6.522 5.025C9.505 39.556 16.227 44 24 44z"></path><path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303c-.792 2.237-2.231 4.166-4.087 5.571l6.19 5.238C42.012 36.494 44 30.861 44 24c0-1.341-.138-2.65-.389-3.917z"></path></svg>
                    </div>
                    <p class="mt-4 text-primary-text/80">Votre avis compte pour nous !</p>
                    <p class="text-sm text-primary-text/60">Partagez votre expérience avec d'autres gourmands.</p>
                    <div class="mt-6">
                        <flux:button
                            variant="primary"
                            color="sky"
                            href="https://g.page/r/CX1Djn48Q0r4EBM/review"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            Lire ou laisser un avis
                        </flux:button>
                    </div>
                </x-card>
            </div>
        </div>
    </section>
</div>
