<footer class="bg-primary-text text-background/80 py-12">
    <div class="container mx-auto px-4 grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
        {{-- Address Section --}}
        <div>
            <h3 class="text-lg mb-2 text-accent-2">Notre Adresse</h3>
            <p class="text-sm">13 boulevard Victor Hugo<br>84350 Courthézon, France</p>
        </div>

        {{-- Contact & Hours Section --}}
        <div>
            <h3 class="text-lg mb-2 text-accent-2">Contact & Horaires</h3>
            <p class="text-sm">
                Téléphone : <a href="tel:+33123456789" class="hover:text-white transition-colors">06 41 13 75 18</a>
            </p>
            <p class="mt-2 text-sm">
                Mercredi - Jeudi<br>
                11:30 - 13:00 & 18:30 - 21:00<br>
                <br>
                Vendredi - Samedi - Dimanche<br>
                18:30 - 21:30
            </p>
        </div>

        {{-- Social Media Section --}}
        <div>
            <h3 class="text-lg mb-2 text-accent-2">Suivez-nous</h3>
            <div class="flex justify-center space-x-4 mt-2">
                <a href="https://www.facebook.com/WendysDinerCourthezon/" aria-label="Facebook" class="hover:text-white transition-colors" target = "_blank">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M18.77 7.46H14.5v-1.9c0-.9.6-1.1 1-1.1h3V.5h-4.33C10.24.5 9.5 3.44 9.5 5.32v2.14H6v4.44h3.5v8.4h4.44v-8.4h3.81l.47-4.44z"></path></svg>
                </a>
                <a href="#" aria-label="Instagram" class="hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M7.8 2h8.4C19.4 2 22 4.6 22 7.8v8.4a5.8 5.8 0 0 1-5.8 5.8H7.8C4.6 22 2 19.4 2 16.2V7.8A5.8 5.8 0 0 1 7.8 2m-.2 2A3.6 3.6 0 0 0 4 7.6v8.8A3.6 3.6 0 0 0 7.6 20h8.8a3.6 3.6 0 0 0 3.6-3.6V7.6A3.6 3.6 0 0 0 16.4 4H7.6m9.65 1.5a1.25 1.25 0 1 1-2.5 0 1.25 1.25 0 0 1 2.5 0M12 7a5 5 0 1 1 0 10 5 5 0 0 1 0-10m0 2a3 3 0 1 0 0 6 3 3 0 0 0 0-6z"></path></svg>
                </a>
            </div>
        </div>
    </div>
    <div class="text-center text-background/60 mt-8 pt-8 border-t border-background/20 text-xs">
        <p>&copy; {{ date('Y') }} Wendy's Diner. Tous droits réservés.</p>
    </div>
</footer>
