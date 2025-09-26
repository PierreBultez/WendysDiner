<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Title;

new #[Title("Infos Pratiques - Wendy's Diner")] class extends Component
{
    //
}; ?>

<div class="bg-background">
    <!-- HERO SECTION -->
    <div class="relative h-96 flex items-center justify-center text-center bg-zinc-800 text-white overflow-hidden">
        <div class="absolute inset-0 z-0">
            <img src="/images/placeholders/diner-contact.jpg" alt="Devanture du Wendy's Diner" class="w-full h-full object-cover opacity-40">
        </div>
        <div class="relative z-10 p-4">
            <x-section-title
                tag="h1"
                title="Infos Pratiques"
                subtitle="Tout ce que vous devez savoir pour nous trouver et nous contacter."
                titleClasses="!text-white"
                subtitleClasses="!text-white/80"
            />
        </div>
    </div>

    <!-- INFO CONTENT -->
    <div class="container mx-auto px-4 py-16 md:py-24">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-16">

            <!-- Left Column: Info & Hours -->
            <div class="space-y-8">
                <div>
                    <h2 class="text-3xl text-accent-1 font-heading">Contact & Adresse</h2>
                    <div class="mt-4 space-y-2 text-primary-text/80">
                        <p class="flex items-center">
                            <flux:icon name="map-pin" class="size-5 mr-3 text-accent-2" />
                            13 boulevard Victor Hugo, 84350 Courthézon, France
                        </p>
                        <p class="flex items-center">
                            <flux:icon name="phone" class="size-5 mr-3 text-accent-2" />
                            {{-- Click-to-call functionality --}}
                            <a href="tel:+33641137518" class="hover:text-accent-1 transition-colors">06 41 13 75 18</a>
                        </p>
                    </div>
                </div>
                <div>
                    <h2 class="text-3xl text-accent-1 font-heading">Horaires d'Ouverture</h2>
                    <div class="mt-4 space-y-2 text-primary-text/80">
                        <p><span class="font-bold w-24 inline-block">Lundi :</span> Fermé</p>
                        <p><span class="font-bold w-24 inline-block">Mardi :</span> Fermé</p>
                        <p><span class="font-bold w-24 inline-block">Mercredi :</span> 11:30 - 13:00 & 18:30 - 21:00</p>
                        <p><span class="font-bold w-24 inline-block">Jeudi :</span> 11:30 - 13:00 & 18:30 - 21:00</p>
                        <p><span class="font-bold w-24 inline-block">Vendredi :</span> 18:30 - 21:30</p>
                        <p><span class="font-bold w-24 inline-block">Samedi :</span> 18:30 - 21:30</p>
                        <p><span class="font-bold w-24 inline-block">Dimanche :</span> 18:30 - 21:30</p>
                    </div>
                </div>
            </div>

            <!-- Right Column: Google Map -->
            <div>
                <div class="w-full h-full min-h-[400px] bg-zinc-200 rounded-lg shadow-xl flex items-center justify-center border-2 border-dashed border-accent-2/50">
                    {{-- This div is where the map will be rendered by JavaScript --}}
                    <div id="map" class="w-full h-full"></div>
                </div>

            </div>
        </div>

        {{-- Google Maps JavaScript API Integration --}}
        @push('scripts')
            <script>
                function initMap() {
                    const mapStyle = [
                        {
                            "featureType": "all",
                            "elementType": "geometry",
                            "stylers": [
                                {
                                    "color": "#ebe3cd"
                                }
                            ]
                        },
                        {
                            "featureType": "all",
                            "elementType": "labels.text.fill",
                            "stylers": [
                                {
                                    "color": "#523735"
                                }
                            ]
                        },
                        {
                            "featureType": "all",
                            "elementType": "labels.text.stroke",
                            "stylers": [
                                {
                                    "color": "#f5f1e6"
                                }
                            ]
                        },
                        {
                            "featureType": "administrative",
                            "elementType": "geometry.stroke",
                            "stylers": [
                                {
                                    "color": "#c9b2a6"
                                }
                            ]
                        },
                        {
                            "featureType": "administrative.land_parcel",
                            "elementType": "geometry.stroke",
                            "stylers": [
                                {
                                    "color": "#dcd2be"
                                }
                            ]
                        },
                        {
                            "featureType": "administrative.land_parcel",
                            "elementType": "labels.text.fill",
                            "stylers": [
                                {
                                    "color": "#ae9e90"
                                }
                            ]
                        },
                        {
                            "featureType": "landscape.natural",
                            "elementType": "geometry",
                            "stylers": [
                                {
                                    "color": "#dfd2ae"
                                }
                            ]
                        },
                        {
                            "featureType": "poi",
                            "elementType": "geometry",
                            "stylers": [
                                {
                                    "color": "#dfd2ae"
                                }
                            ]
                        },
                        {
                            "featureType": "poi",
                            "elementType": "labels.text.fill",
                            "stylers": [
                                {
                                    "color": "#93817c"
                                }
                            ]
                        },
                        {
                            "featureType": "poi.park",
                            "elementType": "geometry.fill",
                            "stylers": [
                                {
                                    "color": "#a5b076"
                                }
                            ]
                        },
                        {
                            "featureType": "poi.park",
                            "elementType": "labels.text.fill",
                            "stylers": [
                                {
                                    "color": "#447530"
                                }
                            ]
                        },
                        {
                            "featureType": "road",
                            "elementType": "geometry",
                            "stylers": [
                                {
                                    "color": "#f5f1e6"
                                }
                            ]
                        },
                        {
                            "featureType": "road.highway",
                            "elementType": "geometry",
                            "stylers": [
                                {
                                    "color": "#f8c967"
                                }
                            ]
                        },
                        {
                            "featureType": "road.highway",
                            "elementType": "geometry.stroke",
                            "stylers": [
                                {
                                    "color": "#e9bc62"
                                }
                            ]
                        },
                        {
                            "featureType": "road.highway.controlled_access",
                            "elementType": "geometry",
                            "stylers": [
                                {
                                    "color": "#e98d58"
                                }
                            ]
                        },
                        {
                            "featureType": "road.highway.controlled_access",
                            "elementType": "geometry.stroke",
                            "stylers": [
                                {
                                    "color": "#db8555"
                                }
                            ]
                        },
                        {
                            "featureType": "road.arterial",
                            "elementType": "geometry",
                            "stylers": [
                                {
                                    "color": "#fdfcf8"
                                }
                            ]
                        },
                        {
                            "featureType": "road.local",
                            "elementType": "labels.text.fill",
                            "stylers": [
                                {
                                    "color": "#806b63"
                                }
                            ]
                        },
                        {
                            "featureType": "transit.line",
                            "elementType": "geometry",
                            "stylers": [
                                {
                                    "color": "#dfd2ae"
                                }
                            ]
                        },
                        {
                            "featureType": "transit.line",
                            "elementType": "labels.text.fill",
                            "stylers": [
                                {
                                    "color": "#8f7d77"
                                }
                            ]
                        },
                        {
                            "featureType": "transit.line",
                            "elementType": "labels.text.stroke",
                            "stylers": [
                                {
                                    "color": "#ebe3cd"
                                }
                            ]
                        },
                        {
                            "featureType": "transit.station",
                            "elementType": "geometry",
                            "stylers": [
                                {
                                    "color": "#dfd2ae"
                                }
                            ]
                        },
                        {
                            "featureType": "water",
                            "elementType": "geometry.fill",
                            "stylers": [
                                {
                                    "color": "#b9d3c2"
                                }
                            ]
                        },
                        {
                            "featureType": "water",
                            "elementType": "labels.text.fill",
                            "stylers": [
                                {
                                    "color": "#92998d"
                                }
                            ]
                        }
                    ];

                    const restaurantLocation = { lat: 44.08606, lng: 4.88629 };

                    // 3. Création de la carte
                    const map = new google.maps.Map(document.getElementById("map"), {
                        center: restaurantLocation,
                        zoom: 16,
                        styles: mapStyle,
                        disableDefaultUI: true, // Cache les contrôles par défaut (zoom, etc.)
                    });

                    // 4. Ajout d'un marqueur personnalisé
                    const marker = new google.maps.Marker({
                        position: restaurantLocation,
                        map: map,
                        title: "Wendy's Diner",
                        icon: {
                            url: "{{ asset('images/map-marker.png') }}",

                            // 2. La taille à laquelle afficher l'icône sur la carte (en pixels)
                            //    Ajustez ces valeurs pour qu'elles correspondent à votre image.
                            scaledSize: new google.maps.Size(50, 50),

                            // 3. Le point d'ancrage de l'icône. C'est le point de l'image
                            //    qui sera aligné avec les coordonnées GPS du restaurant.
                            //    Pour une épingle, c'est souvent le centre en bas.
                            //    Pour un logo rond, le centre est une bonne option.
                            anchor: new google.maps.Point(25, 25) // Centre de l'icône de 50x50
                        }
                    });
                }
            </script>
            <script async defer
                    src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.places_api_key') }}&callback=initMap">
            </script>
        @endpush
    </div>
</div>
