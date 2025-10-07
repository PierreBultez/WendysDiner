<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Fetch categories at once to avoid multiple queries in the loop
        $categories = Category::pluck('id', 'name');

        $products = [
            // --- Nos Burgers Signature (7 produits) ---
            [
                'name' => 'Wendy\'s Classic',
                'description' => 'Le burger qui a fait notre réputation. Steak de bœuf frais, cheddar maturé, salade croquante, tomate, oignons rouges et notre sauce secrète inimitable.',
                'price' => 12.90,
                'image_url' => '/images/products/wendys-classic.png',
                'category_name' => 'Nos Burgers Signature'
            ],
            [
                'name' => 'Baconator Deluxe',
                'description' => 'Pour les amateurs de bacon. Double steak, double cheddar, et six tranches de bacon fumé croustillant. Un monstre de saveur.',
                'price' => 14.50,
                'image_url' => '/images/products/baconator-deluxe.png',
                'category_name' => 'Nos Burgers Signature'
            ],
            [
                'name' => 'Le Provençal',
                'description' => 'Un hommage à notre région. Steak de bœuf, chèvre frais, miel de lavande, roquette et tomates séchées. Un délice sucré-salé.',
                'price' => 13.80,
                'image_url' => '/images/products/le-provencal.png',
                'category_name' => 'Nos Burgers Signature'
            ],
            [
                'name' => 'Burger Végétarien Forestier',
                'description' => 'Galette de légumes de saison, champignons poêlés, sauce à l\'ail des ours, et mozzarella fondante.',
                'price' => 12.50,
                'image_url' => '/images/products/vege-forestier.png',
                'category_name' => 'Nos Burgers Signature'
            ],
            [
                'name' => 'Le Ch\'ti Double Cheese',
                'description' => 'Inspiré du Nord : Double steak, double Maroilles AOP crémeux, oignons confits à la bière blonde. Fort en goût !',
                'price' => 15.20,
                'image_url' => '/images/products/chti-double-cheese.png',
                'category_name' => 'Nos Burgers Signature'
            ],
            [
                'name' => 'Spicy Inferno',
                'description' => 'Pour les palais avertis. Steak de bœuf mariné au piment, cheddar relevé, sauce Sriracha maison et poivrons grillés.',
                'price' => 13.50,
                'image_url' => '/images/products/spicy-inferno.png',
                'category_name' => 'Nos Burgers Signature'
            ],
            [
                'name' => 'Burger Poulet Croustillant',
                'description' => 'Filet de poulet pané maison, coleslaw crémeux, cornichons doux et sauce Ranch.',
                'price' => 11.90,
                'image_url' => '/images/products/poulet-croustillant.png',
                'category_name' => 'Nos Burgers Signature'
            ],

            // --- Accompagnements (13 produits) ---
            [
                'name' => 'Frites Maison Classiques',
                'description' => 'Nos célèbres frites coupées à la main, frites selon la tradition du Nord. Croustillantes et fondantes.',
                'price' => 4.50,
                'image_url' => '/images/products/frites-maison.png',
                'category_name' => 'Accompagnements'
            ],
            [
                'name' => 'Frites Maison au Cheddar',
                'description' => 'Nos frites dorées coupées à la main, nappées d\'une sauce cheddar onctueuse et parsemées de ciboulette fraîche.',
                'price' => 6.50,
                'image_url' => '/images/products/frites-cheddar.png',
                'category_name' => 'Accompagnements'
            ],
            [
                'name' => 'Frites au Piment d\'Espelette',
                'description' => 'Nos frites signature légèrement saupoudrées de Piment d\'Espelette pour une touche de chaleur subtile.',
                'price' => 5.20,
                'image_url' => '/images/products/frites-espelette.png',
                'category_name' => 'Accompagnements'
            ],
            [
                'name' => 'Onion Rings "50s Style"',
                'description' => 'Des rondelles d\'oignon épaisses, panées et frites à la perfection. Croustillantes à l\'extérieur, fondantes à l\'intérieur.',
                'price' => 5.50,
                'image_url' => '/images/products/onion-rings.png',
                'category_name' => 'Accompagnements'
            ],
            [
                'name' => 'Patates Douces Frites',
                'description' => 'Frites de patates douces, légèrement sucrées, parfaites avec notre sauce aigre-douce.',
                'price' => 5.80,
                'image_url' => '/images/products/patates-douces.png',
                'category_name' => 'Accompagnements'
            ],
            [
                'name' => 'Poutine de Courthézon',
                'description' => 'Inspiration québécoise : Frites maison, sauce brune riche et morceaux de fromage frais du Comté.',
                'price' => 8.90,
                'image_url' => '/images/products/poutine-courthezon.png',
                'category_name' => 'Accompagnements'
            ],
            [
                'name' => 'Ailes de Poulet Buffalo (x5)',
                'description' => 'Ailes de poulet marinées et frites, nappées de sauce Buffalo piquante. Servies avec une sauce au bleu.',
                'price' => 7.50,
                'image_url' => '/images/products/ailes-buffalo.png',
                'category_name' => 'Accompagnements'
            ],
            [
                'name' => 'Mini-Salade Coleslaw',
                'description' => 'Chou et carottes râpées dans une sauce crémeuse et acidulée. La fraîcheur à côté de votre burger.',
                'price' => 3.50,
                'image_url' => '/images/products/coleslaw.png',
                'category_name' => 'Accompagnements'
            ],
            [
                'name' => 'Sauce Secrète Wendy\'s',
                'description' => 'Notre sauce signature, parfaite pour tremper vos frites.',
                'price' => 1.00,
                'image_url' => '/images/products/sauce-secrete.png',
                'category_name' => 'Accompagnements'
            ],
            [
                'name' => 'Sauce BBQ Fumé',
                'description' => 'Sauce barbecue maison, riche et fumée.',
                'price' => 1.00,
                'image_url' => '/images/products/sauce-bbq.png',
                'category_name' => 'Accompagnements'
            ],
            [
                'name' => 'Sauce Moutarde & Miel',
                'description' => 'Douce et acidulée, idéale pour le poulet.',
                'price' => 1.00,
                'image_url' => '/images/products/sauce-moutarde-miel.png',
                'category_name' => 'Accompagnements'
            ],
            [
                'name' => 'Cornichons Frits Piquants',
                'description' => 'Cornichons panés et frits, légèrement assaisonnés de paprika fumé. Surprenant et addictif.',
                'price' => 4.90,
                'image_url' => '/images/products/cornichons-frits.png',
                'category_name' => 'Accompagnements'
            ],
            [
                'name' => 'Petite Salade Verte',
                'description' => 'Mélange de jeunes pousses, tomates cerises et vinaigrette légère.',
                'price' => 4.00,
                'image_url' => '/images/products/salade-verte.png',
                'category_name' => 'Accompagnements'
            ],

            // --- Salades Repas (3 produits) ---
            [
                'name' => 'Salade César au Poulet Grillé',
                'description' => 'Laitue romaine, poulet mariné grillé, croûtons à l\'ail, copeaux de Parmesan, et sauce César maison.',
                'price' => 13.90,
                'image_url' => '/images/products/salade-cesar.png',
                'category_name' => 'Salades Repas'
            ],
            [
                'name' => 'Salade Sud-Ouest',
                'description' => 'Mesclun, magret de canard fumé, gésiers confits, tomates, noix et vinaigrette balsamique.',
                'price' => 14.50,
                'image_url' => '/images/products/salade-sudouest.png',
                'category_name' => 'Salades Repas'
            ],
            [
                'name' => 'Salade Végétarienne Quinoa & Féta',
                'description' => 'Quinoa, féta, concombres, poivrons, olives noires, menthe fraîche et citron. Très rafraîchissant.',
                'price' => 12.80,
                'image_url' => '/images/products/salade-quinoa.png',
                'category_name' => 'Salades Repas'
            ],

            // --- Boissons Fraîches (10 produits) ---
            [
                'name' => 'Milkshake Vanille Vintage',
                'description' => 'Un classique intemporel. Crème glacée à la vanille de Madagascar, lait frais, et une touche de chantilly maison.',
                'price' => 7.00,
                'image_url' => '/images/products/milkshake-vanille.png',
                'category_name' => 'Boissons Fraîches'
            ],
            [
                'name' => 'Milkshake Chocolat Noir Intense',
                'description' => 'Le plaisir du cacao. Crème glacée au chocolat noir, lait, et copeaux de chocolat.',
                'price' => 7.50,
                'image_url' => '/images/products/milkshake-chocolat.png',
                'category_name' => 'Boissons Fraîches'
            ],
            [
                'name' => 'Milkshake Fraise des Bois',
                'description' => 'Glace à la fraise, coulis de fraise, lait, garni de fruits rouges frais.',
                'price' => 7.00,
                'image_url' => '/images/products/milkshake-fraise.png',
                'category_name' => 'Boissons Fraîches'
            ],
            [
                'name' => 'Coca-Cola Cherry en bouteille verre',
                'description' => 'Le goût authentique du Coca-Cola avec une note de cerise, servi dans sa bouteille en verre iconique.',
                'price' => 4.50,
                'image_url' => '/images/products/coca-cherry.png',
                'category_name' => 'Boissons Fraîches'
            ],
            [
                'name' => 'Limonade Maison Gingembre-Citron',
                'description' => 'Préparation du jour, pétillante, avec du jus de citron frais et une pointe de gingembre.',
                'price' => 5.00,
                'image_url' => '/images/products/limonade-gingembre.png',
                'category_name' => 'Boissons Fraîches'
            ],
            [
                'name' => 'Thé Glacé Pêche Maison',
                'description' => 'Infusion de thé noir, arôme naturel de pêche, non sucré (possibilité de sirop).',
                'price' => 4.80,
                'image_url' => '/images/products/the-glace-peche.png',
                'category_name' => 'Boissons Fraîches'
            ],
            [
                'name' => 'Jus d\'Orange Pressé',
                'description' => 'Jus d\'orange 100% frais, pressé à la minute.',
                'price' => 5.50,
                'image_url' => '/images/products/jus-orange.png',
                'category_name' => 'Boissons Fraîches'
            ],
            [
                'name' => 'Eau Minérale Plate (50cl)',
                'description' => 'Bouteille d\'eau minérale de source.',
                'price' => 3.00,
                'image_url' => '/images/products/eau-plate.png',
                'category_name' => 'Boissons Fraîches'
            ],
            [
                'name' => 'Eau Gazeuse (50cl)',
                'description' => 'Bouteille d\'eau pétillante rafraîchissante.',
                'price' => 3.00,
                'image_url' => '/images/products/eau-gazeuse.png',
                'category_name' => 'Boissons Fraîches'
            ],
            [
                'name' => 'Root Beer A&W',
                'description' => 'Boisson gazeuse typique des diners américains, avec son goût inimitable.',
                'price' => 4.90,
                'image_url' => '/images/products/root-beer.png',
                'category_name' => 'Boissons Fraîches'
            ],

            // --- Boissons Chaudes (5 produits) ---
            [
                'name' => 'Café Expresso Pur Arabica',
                'description' => 'Un expresso serré, issu d\'une sélection de grains Arabica de qualité.',
                'price' => 2.50,
                'image_url' => '/images/products/expresso.png',
                'category_name' => 'Boissons Chaudes'
            ],
            [
                'name' => 'Double Expresso',
                'description' => 'Pour un coup de fouet : le double de saveur.',
                'price' => 3.50,
                'image_url' => '/images/products/double-expresso.png',
                'category_name' => 'Boissons Chaudes'
            ],
            [
                'name' => 'Latte Macchiato Caramel',
                'description' => 'Lait chaud, expresso, et sirop de caramel, le tout surmonté d\'une mousse de lait légère.',
                'price' => 4.50,
                'image_url' => '/images/products/latte-caramel.png',
                'category_name' => 'Boissons Chaudes'
            ],
            [
                'name' => 'Chocolat Chaud Gourmand',
                'description' => 'Véritable chocolat fondu, lait frais, et une généreuse couche de chantilly maison.',
                'price' => 5.00,
                'image_url' => '/images/products/chocolat-chaud.png',
                'category_name' => 'Boissons Chaudes'
            ],
            [
                'name' => 'Thé Vert Menthe BIO',
                'description' => 'Sélection de thé vert biologique parfumé à la menthe. Rafraîchissant et digestif.',
                'price' => 3.50,
                'image_url' => '/images/products/the-vert-menthe.png',
                'category_name' => 'Boissons Chaudes'
            ],

            // --- Desserts (12 produits) ---
            [
                'name' => 'Brownie Noix de Pécan',
                'description' => 'Moelleux à l\'intérieur, croûte craquante. Servi tiède avec une boule de glace vanille.',
                'price' => 6.50,
                'image_url' => '/images/products/brownie.png',
                'category_name' => 'Desserts Gourmands'
            ],
            [
                'name' => 'Cheesecake New-Yorkais',
                'description' => 'Crème onctueuse sur base de spéculoos, nappée d\'un coulis de fruits rouges.',
                'price' => 7.00,
                'image_url' => '/images/products/cheesecake.png',
                'category_name' => 'Desserts Gourmands'
            ],
            [
                'name' => 'Tarte aux Pommes "Grand-Mère"',
                'description' => 'Tranche de tarte aux pommes classique, chaude, avec une boule de glace caramel beurre salé.',
                'price' => 6.80,
                'image_url' => '/images/products/tarte-pommes.png',
                'category_name' => 'Desserts Gourmands'
            ],
            [
                'name' => 'Cookie Géant Double Chocolat',
                'description' => 'Cookie extra-large, croustillant au bord et fondant au centre, rempli de pépites de chocolat noir et au lait.',
                'price' => 4.50,
                'image_url' => '/images/products/cookie-double-choc.png',
                'category_name' => 'Desserts Gourmands'
            ],
            [
                'name' => 'Sundae Vanille-Pécan',
                'description' => 'Glace à la vanille, sauce caramel chaude, noix de pécan grillées et chantilly.',
                'price' => 6.00,
                'image_url' => '/images/products/sundae-pecan.png',
                'category_name' => 'Desserts Gourmands'
            ],
            [
                'name' => 'Coupe de Glace 2 boules',
                'description' => 'Deux boules de votre choix. Parfums : Vanille, Chocolat, Fraise, Caramel, Citron.',
                'price' => 4.50,
                'image_url' => '/images/products/coupe-glace-2.png',
                'category_name' => 'Desserts Gourmands'
            ],
            [
                'name' => 'Coupe de Glace 3 boules',
                'description' => 'Trois boules de votre choix. Parfums : Vanille, Chocolat, Fraise, Caramel, Citron.',
                'price' => 5.50,
                'image_url' => '/images/products/coupe-glace-3.png',
                'category_name' => 'Desserts Gourmands'
            ],
            [
                'name' => 'Banana Split Classique',
                'description' => 'Banane, 3 boules de glace (vanille, chocolat, fraise), coulis, chantilly et amandes effilées.',
                'price' => 8.50,
                'image_url' => '/images/products/banana-split.png',
                'category_name' => 'Desserts Gourmands'
            ],
            [
                'name' => 'Donut Glaçage Rose',
                'description' => 'Donut moelleux recouvert d\'un glaçage à la fraise et de vermicelles de couleur.',
                'price' => 3.50,
                'image_url' => '/images/products/donut-rose.png',
                'category_name' => 'Desserts Gourmands'
            ],
            [
                'name' => 'Muffin Myrtilles',
                'description' => 'Gros muffin aux myrtilles fraîches, parfait avec un café.',
                'price' => 3.80,
                'image_url' => '/images/products/muffin-myrtilles.png',
                'category_name' => 'Desserts Gourmands'
            ],
            [
                'name' => 'Crème Brûlée à la Lavande',
                'description' => 'Crème onctueuse infusée à la lavande de Provence, croûte de caramel croquante. Une touche locale.',
                'price' => 6.50,
                'image_url' => '/images/products/creme-brulee-lavande.png',
                'category_name' => 'Desserts Gourmands'
            ],
            [
                'name' => 'Waffle Choc-Noisette',
                'description' => 'Gaufre chaude, sauce chocolat, éclats de noisettes et une boule de glace vanille.',
                'price' => 7.20,
                'image_url' => '/images/products/waffle-choc-noisette.png',
                'category_name' => 'Desserts Gourmands'
            ],

            // --- Menus (10 produits) ---
            [
                'name' => 'Menu Wendy\'s Classic',
                'description' => 'Wendy\'s Classic + Frites Maison Classiques + Boisson au choix.',
                'price' => 15.90,
                'image_url' => '/images/products/menu-classic.png',
                'category_name' => 'Menus'
            ],
            [
                'name' => 'Menu Baconator',
                'description' => 'Baconator Deluxe + Frites Maison au Cheddar + Boisson au choix.',
                'price' => 17.50,
                'image_url' => '/images/products/menu-baconator.png',
                'category_name' => 'Menus'
            ],
            [
                'name' => 'Menu Végétarien',
                'description' => 'Burger Végétarien Forestier + Patates Douces Frites + Boisson au choix.',
                'price' => 15.50,
                'image_url' => '/images/products/menu-vege.png',
                'category_name' => 'Menus'
            ],
            [
                'name' => 'Menu Poulet',
                'description' => 'Burger Poulet Croustillant + Onion Rings "50s Style" + Boisson au choix.',
                'price' => 14.90,
                'image_url' => '/images/products/menu-poulet.png',
                'category_name' => 'Menus'
            ],
            [
                'name' => 'Menu Enfant Cheeseburger',
                'description' => 'Petit Cheeseburger + Petite Frites + Jus de Fruit ou Eau.',
                'price' => 9.50,
                'image_url' => '/images/products/menu-enfant-cheese.png',
                'category_name' => 'Menus'
            ],
            [
                'name' => 'Menu Enfant Nuggets',
                'description' => '4 Nuggets de poulet maison + Petite Frites + Jus de Fruit ou Eau.',
                'price' => 9.50,
                'image_url' => '/images/products/menu-enfant-nuggets.png',
                'category_name' => 'Menus'
            ],
            [
                'name' => 'Menu Duo Classique',
                'description' => '2 x Menu Wendy\'s Classic (pour 2 personnes).',
                'price' => 30.00,
                'image_url' => '/images/products/menu-duo-classic.png',
                'category_name' => 'Menus'
            ],
            [
                'name' => 'Menu Famille (4 pers)',
                'description' => '4 Burgers au choix (max 2 Baconator) + Grande Poutine + 4 Boissons.',
                'price' => 59.90,
                'image_url' => '/images/products/menu-famille.png',
                'category_name' => 'Menus'
            ],
            [
                'name' => 'Menu Burger du Jour',
                'description' => 'Le Burger Spécial du Chef (variable) + Frites Maison Classiques + Boisson au choix.',
                'price' => 16.90,
                'image_url' => '/images/products/menu-burger-jour.png',
                'category_name' => 'Menus'
            ],
            [
                'name' => 'Menu Salade César',
                'description' => 'Salade César au Poulet Grillé + Eau Minérale + Cookie Géant.',
                'price' => 17.50,
                'image_url' => '/images/products/menu-salade.png',
                'category_name' => 'Menus'
            ],
        ];

        foreach ($products as $productData) {
            // Find the category ID from the fetched collection
            $categoryId = $categories[$productData['category_name']] ?? null;

            if ($categoryId) {
                Product::create([
                    'name' => $productData['name'],
                    'description' => $productData['description'],
                    'price' => $productData['price'],
                    'image_url' => $productData['image_url'],
                    'category_id' => $categoryId,
                ]);
            }
        }
    }
}
