<?php

return [
    // Provides application-wide services.
    // We recommend using fully-qualified class names whenever possible as
    // service names.
    'dependencies' => [
        // Use 'invokables' for constructor-less services, or services that do
        // not require arguments to the constructor. Map a service name to the
        // class name.
        'invokables' => [
            // Fully\Qualified\InterfaceName::class => Fully\Qualified\ClassName::class,
//            Zend\Expressive\Router\RouterInterface::class => Zend\Expressive\Router\FastRouteRouter::class,
//            Helper\ServerUrlHelper::class => Helper\ServerUrlHelper::class,
        ],


        // Use 'factories' for services provided by callbacks/factory classes.
        'factories' => [
//            Helper\ServerUrlMiddleware::class => Helper\ServerUrlMiddlewareFactory::class,
//            Helper\UrlHelperMiddleware::class => Helper\UrlHelperMiddlewareFactory::class,

//            Application::class => ApplicationFactory::class,
//            Helper\UrlHelper::class => Helper\UrlHelperFactory::class,
        ],


    ],
];
