<?php

return [
    'queue' => env('MAINTENANCE_PDF_QUEUE', 'pdf'),

    /*
    |--------------------------------------------------------------------------
    | Optimización de imágenes para PDF
    |--------------------------------------------------------------------------
    |
    | Esta optimización usa GD. Déjala en false en entornos donde GD no esté
    | instalada. En ese caso, se incrustará la imagen original en base64.
    |
    */
    'optimize_images' => env('MAINTENANCE_PDF_OPTIMIZE_IMAGES', false),

    'max_width' => (int) env('MAINTENANCE_PDF_MAX_WIDTH', 800),
    'max_height' => (int) env('MAINTENANCE_PDF_MAX_HEIGHT', 600),
    'jpeg_quality' => (int) env('MAINTENANCE_PDF_JPEG_QUALITY', 75),
];

