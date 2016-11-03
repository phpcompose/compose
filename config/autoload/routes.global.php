<?php

return [
    'dependencies' => [
        'invokables' => [
            App\Action\PingAction::class => App\Action\PingAction::class,
        ],
        'factories' => [
            App\Action\HomePageAction::class => App\Action\HomePageFactory::class,
        ],
    ],

    'routes' => [
        [
            'name' => 'home',
            'path' => '/',
            'middleware' => App\Action\HomePageAction::class,
            'allowed_methods' => ['GET'],
        ],
        [
            'name' => 'api.ping',
            'path' => '/api/ping',
            'middleware' => App\Action\PingAction::class,
            'allowed_methods' => ['GET'],
        ],
        [
            'name' => 'test.hello',
            'path' => '/test/hello[/{action_params:.+}]',
            'middleware' => \App\Test\Action\HelloAction::class,
            'allowed_methods' => ['GET'],
        ],
    ],

    "paths" => [
        "/test/hey" => \App\Test\Action\HelloAction::class,
    ],
];
