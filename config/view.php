<?php

return [
    'paths' => [
        resource_path('views'),
    ],

    // ❗ Use storage_path() directly (realpath() can return false)
    'compiled' => storage_path('framework/views'),
];
