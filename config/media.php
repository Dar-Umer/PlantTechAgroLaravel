<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Image & Media Compression Settings
    |--------------------------------------------------------------------------
    |
    | Controls automatic image optimization for all photo uploads across
    | the Admin Panel and Mobile Apps (Field Agent and Farmer apps).
    |
    */

    // Enable or disable automatic image compression globally
    'compression_enabled' => true,

    // Maximum bounding width and height (downscales 12-48MP smartphone photos)
    'max_dimension' => 1920,

    // Image compression quality (1 - 100)
    'quality' => 82,

    // Automatically convert JPG/PNG to modern WebP format for optimal space savings
    'convert_to_webp' => true,

    // Auto-orient photos using phone camera EXIF metadata
    'auto_orient' => true,
];
