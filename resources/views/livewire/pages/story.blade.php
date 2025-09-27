<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Title;

new #[Title("Notre Histoire - Wendy's Diner à Courthézon")] class extends Component
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
                subtitle="L'aventure d'un couple du Nord, de frites belges et d'un rêve américain dans le Vaucluse."
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
                <h2 class="text-3xl text-accent-1">Le Goût du Nord, la Passion du Diner</h2>
                <p class="mt-4 text-primary-text/80">
                    Nous sommes Wendy et Pierre. Originaires du Nord de la France, nous sommes tombés amoureux du Vaucluse, mais une chose nous manquait cruellement : les vraies frites de chez nous, celles cuites au gras de bœuf. En parallèle, nous partagions une passion pour l'ambiance unique des diners américains des années 50, où nous avons célébré notre première St Valentin.
                </p>
                <p class="mt-2 text-primary-text/80">
                    En 2022, l'idée folle est née : et si on combinait les deux ? Ouvrir notre propre resto de burgers qualitatifs, accompagnés de frites authentiques. Après des recherches de local infructueuses à Courthézon, notre commune de cœur, nous avons lancé Wendy's Diner en livraison. Le succès a été immédiat et a dépassé toutes nos attentes.
                </p>
            </div>
            <div class="order-1 md:order-2">
                <img src="/images/placeholders/diner-founder.jpg" alt="Wendy et Dave, les fondateurs" class="rounded-lg shadow-xl w-full">
            </div>
        </section>

        <!-- Section 2: The Rebirth -->
        <section class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
            <div>
                <img src="/images/placeholders/diner-kitchen.jpg" alt="Cuisine du Wendy's Diner" class="rounded-lg shadow-xl w-full">
            </div>
            <div>
                <h2 class="text-3xl text-accent-1">Une Seconde Chance, Grâce à Vous</h2>
                <p class="mt-4 text-primary-text/80">
                    Mais la vie nous a mis à l'épreuve, et nous avons dû prendre la difficile décision de mettre notre aventure en pause pour nous consacrer à notre famille. Nous avons tout arrêté, mais une chose est restée : la page Facebook, et le lien incroyable avec vous, nos clients.
                </p>
                <p class="mt-2 text-primary-text/80">
                    Avance rapide jusqu'en mai 2025. Le local de nos rêves, à Courthézon, se libère. L'étincelle s'est ravivée. Nous avons lancé une cagnotte participative, un appel à notre communauté. Votre réponse a été phénoménale et a dépassé nos espérances. Grâce à vous, Wendy's Diner n'était pas mort. Le 14 juillet 2025, nous avons pu rouvrir nos portes, pour de vrai cette fois.
                </p>
            </div>
        </section>
    </div>

    <!-- SEO & CONCEPT SECTION -->
    <div class="bg-zinc-100 dark:bg-zinc-800 py-16">
        <div class="container mx-auto px-4 text-center">
            <x-section-title
                tag="h2"
                title="Notre Promesse : Qualité & Authenticité"
                subtitle="Bien plus qu'un simple fast-food."
            />
            <div class="mt-8 max-w-4xl mx-auto text-primary-text/80 space-y-4">
                <p>
                    Aujourd'hui, Wendy's Diner est la concrétisation de ce parcours. Notre concept est simple : vous proposer un <strong>restaurant</strong> avec les meilleurs <strong>burgers</strong> possible, faits avec des produits de qualité : buns artisanaux et locaux, vrais steaks de bœuf généreux, fromages AOP comme le Maroilles ou le Saint-Nectaire. Et bien sûr, nos fameuses <strong>frites</strong> fraîches, épluchées chaque jour et cuites au gras de bœuf, comme le veut la tradition. Que vous cherchiez un bon <strong>resto</strong> ou un <strong>snack</strong> gourmand, nous mettons toute notre passion dans vos assiettes.
                </p>
                <p class="font-bold">
                    Vous cherchez les meilleurs <strong class="text-accent-1">burgers</strong> de <strong class="text-accent-1">Courthézon</strong> ? Un <strong class="text-accent-1">restaurant</strong> pour des <strong class="text-accent-1">frites</strong> maison près d'<strong>Orange</strong> ou <strong>Jonquières</strong> ? Ou un petit <strong class="text-accent-1">diner</strong> dans le Vaucluse ? Wendy's Diner est votre nouvelle destination.
                </p>
            </div>
        </div>
    </div>
</div>
