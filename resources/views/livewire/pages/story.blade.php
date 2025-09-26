<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Title;

new #[Title("Notre Histoire - Wendy's Diner")] class extends Component
{
    //
}; ?>

<div class="bg-background">
    <!-- =================================================================== -->
    <!-- HERO SECTION FOR THE STORY PAGE                                     -->
    <!-- =================================================================== -->
    <div class="relative h-96 flex items-center justify-center text-center bg-zinc-800 text-white overflow-hidden">
        <div class="absolute inset-0 z-0">
            <img src="/images/placeholders/diner-old-photo.jpg" alt="Photo d'archive du diner" class="w-full h-full object-cover opacity-40">
        </div>
        <div class="relative z-10 p-4">
            <x-section-title
                tag="h1"
                title="Notre Histoire"
                subtitle="Plus qu'un restaurant, une passion familiale."
                titleClasses="!text-white"
                subtitleClasses="!text-white/80"
            />
        </div>
    </div>

    <!-- =================================================================== -->
    <!-- STORY CONTENT                                                       -->
    <!-- =================================================================== -->
    <div class="container mx-auto px-4 py-16 md:py-24 space-y-16">

        <!-- Section 1: The Beginning -->
        <section class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
            <div class="order-2 md:order-1">
                <h2 class="text-3xl text-accent-1">1958 : Le Rêve Américain</h2>
                <p class="mt-4 text-primary-text/80">
                    Tout a commencé avec un rêve. Celui de Wendy et Dave, un couple passionné par l'Amérique des années 50, ses chromes étincelants, son rock'n'roll endiablé et ses saveurs authentiques. En 1958, ils ouvrent leur premier "Diner" dans une petite rue de Vintage City, avec une seule mission : servir le meilleur burger de la ville, avec le sourire.
                </p>
                <p class="mt-2 text-primary-text/80">
                    Le succès fut immédiat. Les habitants affluaient pour goûter au "Classique Wendy's", une recette simple mais parfaite, transmise de génération en génération.
                </p>
            </div>
            <div class="order-1 md:order-2">
                <img src="/images/placeholders/diner-founder.jpg" alt="Wendy et Dave, les fondateurs" class="rounded-lg shadow-xl w-full">
            </div>
        </section>

        <!-- Section 2: The Legacy -->
        <section class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
            <div>
                <img src="/images/placeholders/diner-kitchen.jpg" alt="Cuisine du Wendy's Diner" class="rounded-lg shadow-xl w-full">
            </div>
            <div>
                <h2 class="text-3xl text-accent-1">Aujourd'hui : La Tradition Continue</h2>
                <p class="mt-4 text-primary-text/80">
                    Plus de 60 ans plus tard, c'est leur petit-fils, Pierre, qui est aux commandes. La passion est restée la même, et les recettes aussi ! Chaque matin, la viande est hachée sur place, les frites sont coupées à la main et les milkshakes sont préparés avec de la vraie crème glacée.
                </p>
                <p class="mt-2 text-primary-text/80">
                    Nous sommes fiers de perpétuer cet héritage, en vous offrant non seulement un repas, mais un véritable voyage dans le temps. Bienvenue chez vous.
                </p>
            </div>
        </section>

    </div>
</div>
