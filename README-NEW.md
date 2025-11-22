# Wendy's Diner

Ce projet est une application web moderne conçue pour le restaurant "Wendy's Diner". Elle fonctionne comme un système de point de vente (POS) et une plateforme de gestion de commandes, construite avec la stack TALL (Tailwind CSS, Alpine.js, Livewire, Laravel) dans ses versions les plus récentes.

## Table des matières

- [Stack Technique](#stack-technique)
- [Fonctionnalités Principales](#fonctionnalités-principales)
- [Prérequis](#prérequis)
- [Installation](#installation)
- [Utilisation](#utilisation)
- [Lancer les tests](#lancer-les-tests)
- [Déploiement](#déploiement)
- [Structure des dossiers](#structure-des-dossiers)

## Stack Technique

- **Backend**:
  - PHP 8.4
  - Laravel 12
  - Livewire 3 (avec Volt & Flux)
  - Laravel Fortify (pour l'authentification)
- **Frontend**:
  - Tailwind CSS 4
  - Alpine.js
  - Vite
- **Base de données**:
  - SQLite pour le développement local
  - Compatible avec MySQL, PostgreSQL
- **Tests**:
  - Pest
- **Déploiement**:
  - GitHub Actions pour l'intégration et le déploiement continus (CI/CD).

## Fonctionnalités Principales

- Gestion de l'authentification des utilisateurs (Inscription, Connexion, 2FA).
- Tableau de bord d'administration.
- Système de Point de Vente (POS).
- Gestion des produits et des catégories.
- Prise et suivi des commandes.
- Gestion des paiements.

## Prérequis

Avant de commencer, assurez-vous d'avoir les outils suivants installés sur votre machine :
- PHP 8.4 ou supérieur
- Composer
- Node.js et npm (ou yarn)
- Une base de données (ex: SQLite, MySQL, etc.)

## Installation

1.  **Clonez le dépôt :**
    ```bash
    git clone https://github.com/votre-utilisateur/wendys-diner.git
    cd wendys-diner
    ```

2.  **Installez les dépendances PHP :**
    ```bash
    composer install
    ```

3.  **Créez votre fichier d'environnement :**
    ```bash
    cp .env.example .env
    ```

4.  **Générez la clé d'application :**
    ```bash
    php artisan key:generate
    ```

5.  **Configurez la base de données :**
    Modifiez le fichier `.env` avec les informations de connexion à votre base de données. Pour le développement local, vous pouvez laisser la configuration SQLite par défaut.

6.  **Exécutez les migrations et les seeders :**
    Cela créera la structure de la base de données et la remplira avec des données initiales.
    ```bash
    php artisan migrate --seed
    ```

7.  **Installez les dépendances Node.js :**
    ```bash
    npm install
    ```

## Utilisation

Pour lancer l'application en mode développement, vous pouvez utiliser le script `dev` fourni qui lance simultanément le serveur PHP, le watcher de la file d'attente, les logs et Vite.

```bash
npm run dev
```

L'application sera accessible à l'adresse `http://127.0.0.1:8000`.

## Lancer les tests

Pour exécuter la suite de tests automatisés, utilisez la commande suivante :

```bash
php artisan test
```

## Déploiement

Le déploiement est automatisé via un workflow GitHub Actions défini dans `.github/workflows/deploy.yml`. Chaque `push` sur la branche `master` déclenche un déploiement sur le serveur de production.

Le processus inclut :
- Installation des dépendances de production.
- Compilation des assets frontend.
- Exécution des migrations de la base de données.
- Mise en cache de la configuration et des routes pour des performances optimales.

## Structure des dossiers

Le projet suit la structure standard de Laravel, avec quelques points notables :

- `app/Livewire/`: Contient la majorité des composants d'interface utilisateur (logique et vues), en utilisant la structure de `Volt` (fichiers `*.php` uniques).
- `app/Models/`: Contient les modèles Eloquent (`Product`, `Order`, `Category`, etc.).
- `database/migrations/`: Définit la structure de la base de données.
- `resources/views/`: Contient les layouts principaux et les vues Blade traditionnelles.
- `routes/web.php`: Définit les routes de l'application.
- `tests/`: Contient les tests `Feature` et `Unit` écrits avec Pest.
