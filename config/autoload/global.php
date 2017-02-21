<?php

return [
    // disable debugging on production
    'debug' => true,

    'config_cache_enabled' => false,

    'zend-expressive' => [
        'error_handler' => [
            'template_404'   => 'error::404',
            'template_error' => 'error::error',
        ],
    ],

    "autoload" => [
        'testging.config' => 'src/App/Test/config.php'
    ],


];
