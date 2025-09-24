<?php
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.admin')] #[Title('Admin Dashboard')] class extends Component {
    //
}; ?>

<div class="container mx-auto px-4 py-12">
    <h1 class="text-4xl text-accent-1">Tableau de Bord Administrateur</h1>
    <p class="mt-4">Bienvenue dans la section d'administration. Le contenu viendra ici.</p>
</div>
