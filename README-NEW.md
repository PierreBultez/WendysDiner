# Wendy's Diner

Wendy's Diner est une application web moderne de gestion de restaurant (Diner américain) incluant un site vitrine, un système de commande en ligne (Click & Collect / Livraison), une interface d'administration complète et un système de caisse (POS).

Ce projet est construit avec **Laravel 12**, **Livewire 3 (Volt)** et **Tailwind CSS (Flux UI)**.

## 🚀 Fonctionnalités Principales

### 🌐 Partie Publique (Frontend)
*   **Site Vitrine :** Présentation du restaurant, histoire, informations pratiques.
*   **Menu Interactif :** Consultation de la carte des produits et menus.
*   **Commande en Ligne :**
    *   Panier d'achat dynamique.
    *   Choix du mode de retrait : Click & Collect (créneaux horaires) ou Livraison.
    *   Paiement en ligne sécurisé via **Revolut Merchant API**.
    *   Validation de commande en temps réel.
*   **Espace Client :**
    *   Création de compte et connexion sécurisée.
    *   Historique des commandes.
    *   **Système de Fidélité :** Cumul de points à chaque commande.
    *   Gestion du profil (informations personnelles, mot de passe, 2FA).

### 🛠️ Administration (Backend)
*   **Tableau de Bord :** Vue d'ensemble des ventes et statistiques.
*   **Gestion du Catalogue :**
    *   Produits (Burger, Boissons, Desserts, etc.) avec gestion des stocks et disponibilité.
    *   Catégories avec tri par position.
*   **Gestion des Commandes :**
    *   Suivi des commandes en temps réel (En attente, En cuisine, Prêt, Livré).
    *   Détails complets des commandes clients.
*   **Point de Vente (POS) :**
    *   Interface optimisée pour la prise de commande sur place (tablette/écran tactile).
    *   Sélection rapide des produits.
    *   Encaissement multi-méthodes (Espèces, CB).

## 💻 Stack Technique

*   **Framework Backend :** [Laravel 12](https://laravel.com)
*   **Frontend & Interactivité :** [Livewire 3](https://livewire.laravel.com) avec [Volt](https://livewire.laravel.com/docs/volt) (API fonctionnelle pour les composants).
*   **UI Kit :** [Flux UI](https://fluxui.dev) (Composants Tailwind modernes).
*   **Styling :** [Tailwind CSS](https://tailwindcss.com).
*   **Base de Données :** SQLite (par défaut) / MySQL / PostgreSQL.
*   **Paiement :** Revolut Merchant API.
*   **Authentification :** Laravel Fortify (Logique) & Livewire (Vues).

## 📂 Structure de la Base de Données

*   `users`: Clients et Administrateurs (`is_admin`, `loyalty_points`).
*   `products`: Articles du menu (`name`, `price`, `category_id`, `image`, `is_available`).
*   `categories`: Classification des produits (`name`, `type`, `position`).
*   `orders`: Commandes clients (`user_id`, `total_amount`, `status`, `pickup_time`, `delivery_method`).
*   `order_items`: Détail des produits commandés (`order_id`, `product_id`, `quantity`, `unit_price`, `components`).
*   `payments`: Historique des transactions (`order_id`, `amount`, `method`, `status`).

## ⚙️ Installation

1.  **Prérequis :** PHP 8.2+, Composer, Node.js & NPM.
2.  **Cloner le dépôt :**
    ```bash
    git clone <url-du-repo>
    cd wendys-diner
    ```
3.  **Installer les dépendances :**
    ```bash
    composer install
    npm install
    ```
4.  **Configuration de l'environnement :**
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```
    *   Configurez votre base de données dans `.env`.
    *   Configurez l'API Revolut (`REVOLUT_API_KEY`, `REVOLUT_MODE`).
5.  **Migrations et Seeders :**
    ```bash
    php artisan migrate --seed
    ```
    *   Cela créera un utilisateur admin par défaut (voir `DatabaseSeeder`).
6.  **Lancer le serveur de développement :**
    ```bash
    npm run dev
    php artisan serve
    ```

## 🔐 Comptes de Démonstration

*   **Admin :**
    *   Email : `admin@wendys.com`
    *   Mot de passe : `password`
*   **Client :**
    *   Créez un compte depuis la page d'inscription.

## 🧪 Tests

Le projet inclut des tests automatisés (Pest PHP).
```bash
php artisan test
```

## 📝 Auteur

Développé pour Wendy's Diner.