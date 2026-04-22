<?php

// Einfache Konfiguration für die Halle
define('HALL_NAME', 'VibeSeat Hall');
define('RESERVATION_TIMEOUT_MINUTES', 10);

// Einfache Event-Liste
$events = [
    [
        'id' => 1,
        'title' => 'Starlight Cinema Night',
        'category' => 'Kino',
        'description' => 'Ein inspirierender Film mit atemberaubenden Bildern. Perfekt für alle, die gute Filmkunst schätzen.',
        'price' => 14.90,
        'theme' => 'nachtblau',
        'shows' => [
            ['id' => '1-1', 'event_id' => 1, 'display' => 'Heute · 18:00'],
            ['id' => '1-2', 'event_id' => 1, 'display' => 'Heute · 20:15'],
            ['id' => '1-3', 'event_id' => 1, 'display' => 'Morgen · 19:30']
        ]
    ],
    [
        'id' => 2,
        'title' => 'John Pork Live',
        'category' => 'Konzert',
        'description' => 'Ein energiegeladenes Konzerterlebnis mit den Top-Hits. Perfekt für Musikliebhaber.',
        'price' => 39.90,
        'theme' => 'bordeaux',
        'shows' => [
            ['id' => '2-1', 'event_id' => 2, 'display' => 'Fr · 19:30'],
            ['id' => '2-2', 'event_id' => 2, 'display' => 'Sa · 20:00']
        ]
    ],
    [
        'id' => 3,
        'title' => 'Brainrot Theater',
        'category' => 'Theater',
        'description' => 'Ein provokantes Theaterstück, das zum Nachdenken anregt. Für Kulturbegeisterte.',
        'price' => 27.50,
        'theme' => 'dunkelgrau',
        'shows' => [
            ['id' => '3-1', 'event_id' => 3, 'display' => 'Sa · 17:00'],
            ['id' => '3-2', 'event_id' => 3, 'display' => 'Sa · 20:00'],
            ['id' => '3-3', 'event_id' => 3, 'display' => 'So · 18:30']
        ]
    ],
    [
        'id' => 4,
        'title' => 'HTL Leonding Presentation',
        'category' => 'Kino',
        'description' => 'Ein visuelles Spektakel aus der Schule. Für Kunstliebhaber und Schüler.',
        'price' => 12.50,
        'theme' => 'oliv',
        'shows' => [
            ['id' => '4-1', 'event_id' => 4, 'display' => 'Mi · 18:30'],
            ['id' => '4-2', 'event_id' => 4, 'display' => 'Do · 21:00']
        ]
    ],
    [
        'id' => 5,
        'title' => 'Moonlight Cinema Night',
        'category' => 'Kino',
        'description' => 'Ein stilvoller Filmabend unter dem Mond. Perfekt zum Entspannen und Genießen.',
        'price' => 16.90,
        'theme' => 'dunkelgrau',
        'shows' => [
            ['id' => '5-1', 'event_id' => 5, 'display' => 'Morgen · 20:00']
        ]
    ]
];

// Filter by ID / SHOW
function getEventById($eventId) {
    global $events;

    foreach ($events as $event) {
        if ($event['id'] == $eventId) {
            return $event;
        }
    }

    return null;
}

function getShowById($showId) {
    global $events;

    foreach ($events as $event) {
        // hilfe von ki: ?? [] verhindert Warnings, falls ein Event einmal kein 'shows' hat
        foreach ($event['shows'] ?? [] as $show) {
            if (($show['id'] ?? '') == $showId) {
                return $show;
            }
        }
    }
    return null;
}