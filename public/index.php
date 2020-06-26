<?php
if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}


/* Ver: YouTube - Authentication with Slim 3 */
/* https://www.youtube.com/watch?v=0hKciR_dJAk&index=12&list=PLfdtiltiRHWGc_yY90XRdq6mRww042aEC */

use Respect\Validation\Validator as v;

require __DIR__ . '/../vendor/autoload.php';

session_start();

// Instantiate the app
$settings = require __DIR__ . '/../src/settings.php';
$app = new \Slim\App($settings);

// Set up dependencies
require __DIR__ . '/../src/dependencies.php';

// Register middlewares
require __DIR__ . '/../src/middleware.php';

/* Authentication with Slim 3: Custom validation rules (14/29) - 3' 11'' */
v::with('App\\Validation\\Rules\\');

// Register routes
require __DIR__ . '/../src/routes.php';

// Run app
$app->run();
