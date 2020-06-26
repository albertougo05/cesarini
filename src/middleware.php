<?php
// Application middleware

// e.g: $app->add(new \Slim\Csrf\Guard);

$app->add(new \App\Middleware\ValidationErrorsMiddleware($container));

// Lo pasé a grupo de rutas que lo usan
//$app->add(new \App\Middleware\OldInputMiddleware($container));
