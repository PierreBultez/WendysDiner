<?php

return [

    /*
    |--------------------------------------------------------------------------
    | POS Settings
    |--------------------------------------------------------------------------
    |
    | This section contains settings specific to the Point of Sale system.
    |
    */

    'pos' => [

        /**
         * The amount to add to a burger's price to calculate the menu price.
         * Le montant à ajouter au prix d'un burger pour calculer le prix du menu.
         */
        'menu_surcharge' => 4.00,

    ],

    /*
    |--------------------------------------------------------------------------
    | Loyalty Program
    |--------------------------------------------------------------------------
    */
    'loyalty' => [
        1 => ['points' => 30,  'name' => 'Le Grignoteur',       'reward' => 'Un petit plaisir'],
        2 => ['points' => 60,  'name' => 'L\'Affamé',           'reward' => 'Un petit burger'],
        3 => ['points' => 90,  'name' => 'L\'Habitué',          'reward' => 'Un menu classic'],
        4 => ['points' => 120, 'name' => 'Le Gros Bonnet',      'reward' => 'Un menu premium'],
        5 => ['points' => 150, 'name' => 'La Légende', 'reward' => 'Un menu signature'],
    ],

];
