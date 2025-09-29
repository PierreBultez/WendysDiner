---

## Workflow : Système POS pour Wendy's Diner

**Objectif Global :** Créer une interface de caisse enregistreuse tactile, intégrée au dashboard, pour gérer la prise de commandes, les paiements (y compris multiples), et le suivi des commandes.

---

### **PHASE P1 : Fondations - Modèles & Base de Données**
*L'objectif est de créer la structure en base de données nécessaire pour stocker les commandes et les paiements.*

#### P1.1 : Modèle `Order`
- [X] Créer le modèle **Order** et sa migration (`php artisan make:model Order -m`).
- [X] Définir les colonnes dans la migration : `id`, `total_amount`, `status` (string, default: 'en cours'), `notes` (text, nullable), `timestamps`.

#### P1.2 : Modèle `OrderItem`
- [X] Créer le modèle **OrderItem** et sa migration (`php artisan make:model OrderItem -m`).
- [X] Définir les colonnes : `id`, `order_id` (foreign key), `product_id` (foreign key), `quantity`, `unit_price`, `notes` (string, nullable).

#### P1.3 : Modèle `Payment`
- [X] Créer le modèle **Payment** et sa migration (`php artisan make:model Payment -m`).
- [X] Définir les colonnes : `id`, `order_id` (foreign key), `amount`, `method` (string, ex: 'carte', 'espèces').

#### P1.4 : Définir les Relations Eloquent
- [X] Définir la relation `items()` (`hasMany`) dans le modèle **Order**.
- [X] Définir la relation `payments()` (`hasMany`) dans le modèle **Order**.
- [X] Définir la relation `orderItems()` (`hasMany`) dans le modèle **Product**.
- [X] Définir la relation `order()` (`belongsTo`) dans le modèle **OrderItem**.
- [X] Définir la relation `product()` (`belongsTo`) dans le modèle **OrderItem**.
- [X] Définir la relation `order()` (`belongsTo`) dans le modèle **Payment**.

---

### **PHASE P2 : Logique Métier - Gestion des Menus**
*L'objectif est de permettre à notre système de comprendre ce qu'est un "menu" et comment le tarifer.*

#### P2.1 : Identifier les Types de Produits
- [X] Créer une migration pour ajouter la colonne `type` (string, nullable) à la table `categories`.
- [X] Mettre à jour le back-office des catégories pour pouvoir éditer ce nouveau champ `type`.
- [X] Mettre à jour le seeder de catégories pour inclure les types ('burger', 'boisson', 'accompagnement', 'sauce').

#### P2.2 : Définir la Règle de Prix du Menu
- [X] Ajouter une entrée de configuration pour le surcoût du menu (ex: `config/wendys.php` avec la valeur `4.00`).

---

### **PHASE P3 : Le Cœur - L'Interface de Caisse (POS)**
*L'objectif est de créer l'interface principale de prise de commande.*

#### P3.1 : Création de la Page
- [X] Créer la route `/dashboard/pos` et le composant Volt `admin.pos.index`.
- [X] Ajouter un lien "Caisse" dans la sidebar de l'administration.

#### P3.2 : Layout de l'Interface
- [X] Concevoir la structure HTML/Blade en deux colonnes (sélection produits / panier).
- [X] Assurer que le design est "tactile friendly" (gros boutons, etc.).

#### P3.3 : Sélection des Produits
- [X] Afficher les produits sous forme de cartes cliquables, groupés par catégorie.
- [X] Ajouter une barre de filtres par catégorie pour naviguer rapidement.

#### P3.4 : Logique du Panier
- [X] Gérer le panier comme une propriété `array` dans le composant Livewire.
- [X] Implémenter les méthodes : `addToCart()`, `removeFromCart()`, `incrementQuantity()`, `decrementQuantity()`, `addNote()`.

#### P3.5 : Implémentation du Flux "Menu"
- [X] Lors du clic sur un produit de type 'burger', ouvrir une modale : "Seul ou en Menu ?".
- [X] Si "Menu" est choisi, guider l'utilisateur dans une seconde modale pour choisir l'accompagnement, la sauce, puis la boisson.
- [X] Ajouter le "menu" comme un groupe d'articles dans le panier avec le prix correctement calculé.

#### P3.6 : Finalisation et Paiement
- [X] Créer le bouton "Payer" qui ouvre la modale de paiement.
- [X] Afficher le total à payer et le montant restant dû.
- [X] Permettre d'ajouter plusieurs lignes de paiement (`addPaymentLine()`).
- [X] Pour les paiements en espèces, un champ "Montant donné" calculera et affichera la monnaie à rendre.

#### P3.7 : Enregistrement de la Commande
- [X] Créer la méthode `saveOrder()` qui enregistre `Order`, `OrderItems`, et `Payments` en base de données.
- [X] Utiliser une transaction de base de données pour garantir l'intégrité des données.
- [X] Réinitialiser l'interface pour la commande suivante.

---

### **PHASE P4 : Suivi - La Page des Commandes**
*L'objectif est de pouvoir consulter et gérer les commandes passées.*

#### P4.1 : Création de la Page
- [X] Créer la route `/dashboard/orders` et le composant Volt `admin.orders.index`.
- [X] Ajouter le lien "Commandes" dans la sidebar.

#### P4.2 : Listing des Commandes
- [X] Afficher la liste des commandes du jour dans un tableau (ID, Heure, Total, Statut).
- [ ] Ajouter un filtre pour voir les commandes des jours précédents.

#### P4.3 : Gestion du Statut
- [ ] Ajouter des boutons sur chaque ligne pour changer le statut de "En cours" à "Terminée".

---

### **PHASE P5 : Analyse - Suivi des Chiffres (Étape Future)**
*L'objectif est d'exploiter les données collectées pour fournir des statistiques.*

#### P5.1 : Dashboard de Ventes
- [ ] Créer une nouvelle page `/dashboard/analytics`.
- [ ] Afficher des chiffres clés : chiffre d'affaires du jour, nombre de commandes, panier moyen.

#### P5.2 : Analyse des Marges
- [ ] Créer une migration pour ajouter un champ `cost_price` (nullable) à la table `products`.
- [ ] Mettre à jour le back-office des produits pour gérer ce champ.
- [ ] Calculer et afficher la marge brute par jour/semaine/mois.

---
