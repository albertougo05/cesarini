<?php

namespace App\Middleware;

/**
 * Para verificar el ingreso a todas que el usuarios esté logueado
 * 
 */
class AuthMiddleware extends Middleware
{
    public function __invoke($request, $response, $next)
    {
        if (!$this->container->auth->check()) {
            # Si el usuario no está logueado...
            $this->container->flash->addMessage('error', 'Acceso incorrecto. Ingrese sus datos.');
            return $response->withRedirect($this->container->router->pathFor('login'));
        }

        $response = $next($request, $response);
        return $response;
    }
}