<?php

return [
    'common' => [
        'show' => 'Bekijken',
        'actions' => 'Acties',
        'create' => 'Aanmaken',
        'edit' => 'Bewerken',
        'update' => 'Veranderen',
        'new' => 'Nieuw',
        'cancel' => 'Annuleren',
        'attach' => 'Koppelen',
        'detach' => 'Ontkoppelen',
        'save' => 'Opslaan',
        'delete' => 'Verwijderen',
        'delete_selected' => 'Verwijder geselecteerde',
        'search' => 'Zoeken...',
        'back' => 'Terug naar overzicht',
        'are_you_sure' => 'Weet je het zeker?',
        'no_items_found' => 'Geen resultaten gevonden',
        'created' => 'Succesvol aangemaakt',
        'saved' => 'Succesvol opgeslagen',
        'removed' => 'Succesvol verwijdert',
    ],

    'user' => [
        'change_in_credits' => 'De credits van ":user" veranderen met:',
        'change_in_credits_description' => 'Bij positieve waarde worden credits toegevoegd, bij negatieve waarde worden credits afgetrokken.',
        'change_credits' => 'credits veranderen',
    ],

    'screens' => [
        'view' => 'Bekijk',
    ],

    'slides' => [
        'title' => 'titel',
        'approved' => 'goedgekeurd',
        'preview' => 'voorbeeld',

        'still_a_wip' => 'Deze slide is nog in ontwikkeling en kan nog niet worden goedgekeurd.',
        'ask_for_approval' => 'Vraag goedkeuring',
        'revoke_ask_for_approval' => 'Verzoek om goedkeuring intrekken',
        'pending_approval' => 'in afwachting van goedkeuring',

        'displays_from' => 'zichtbaar vanaf',
        'displays_until' => 'zichtbaar tot',
        'activator' => 'geactiveerd door',
        'slides_count' => 'Aantal slides',

        'display_forever' => 'Voor altijd weergeven',
        'slide_duration' => 'Specifieke slide duur',

        'slide_is_active' => 'Jouw slide \':slide\' is actief en wordt weergegeven op de schermen.',
        'slide_opwaarderen' => 'Javascript op slide activeren',
        'slide_opwaarderen_gebruikt' => 'Er is al een slide opgewaardeerd met JavaScript!',
    ],

    'shop_items' => [
        'cost_in_credits' => 'Kosten in credits',
        'limit_purchases' => 'Aankopen beperken',
        'max_per_user' => 'Maximaal aantal per gebruiker',

        'purchase' => 'kopen',
        'purchase_log_reason' => 'Aankoop van \':item\' in winkel',

        'purchase_confirmation' => 'Aankoop bevestigen',
        'purchase_confirmation_description' => 'Weet je zeker dat je \':item\' wilt kopen voor :credits credits?',
        'purchase_confirmation_button' => 'Kopen',

        'purchase_disabled_reasons' => [
            'insufficient_credits' => 'onvoldoende voor aankoop',
            'max_per_user' => 'maximaal aantal bereikt',
        ],

        'purchase_success' => 'Je hebt \':item\' gekocht',
        'purchase_failed' => 'Aankoop mislukt, :reason',
        'purchase_failed_reasons' => [
            'insufficient_credits' => 'onvoldoende credits',
            'max_per_user' => 'maximaal aantal bereikt',
        ],

        'out_of_time' => 'Je hebt geen tijd meer over op dit item',

        'required_type' => 'Alleen voor type gebruiker',
        'required_type_options' => [
            'no_restriction' => 'geen beperking',
            'student' => 'student',
            'teacher' => 'docent',
        ],
    ],
];
