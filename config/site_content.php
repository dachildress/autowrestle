<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Editable site content sections
    |--------------------------------------------------------------------------
    | Keys are used in views via content('key'). 'default' is used when no value
    | is stored in the database. 'type' is 'text' or 'image'. 'label' is for admin UI.
    */
    'sections' => [
        'home.hero.title' => [
            'label' => 'Home hero title',
            'type' => 'text',
            'default' => 'AutoWrestle',
        ],
        'home.hero.subtitle' => [
            'label' => 'Home hero subtitle',
            'type' => 'text',
            'default' => 'Wrestling tournament management made simple.',
        ],
        'home.hero.image' => [
            'label' => 'Home hero image',
            'type' => 'image',
            'default' => null,
        ],
        'home.features.intro' => [
            'label' => 'Home features intro text',
            'type' => 'text',
            'default' => 'Manage tournaments, brackets, and results in one place.',
        ],
        'registration.instructions' => [
            'label' => 'Registration instructions',
            'type' => 'text',
            'default' => 'Select a tournament below to register your wrestlers.',
        ],
        'tournaments.banner' => [
            'label' => 'Tournaments page banner text',
            'type' => 'text',
            'default' => null,
        ],
        'footer.text' => [
            'label' => 'Footer text',
            'type' => 'text',
            'default' => 'AutoWrestle. Wrestling tournament management.',
        ],
        'site.logo' => [
            'label' => 'Site logo (navbar)',
            'type' => 'image',
            'default' => null,
        ],
    ],
];
