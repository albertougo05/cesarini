<?php

namespace App\Middleware;

/**
 * Mantiene los valores de los inputs en formularios
 *
 */
class OldInputMiddleware extends Middleware
{
	
	public function __invoke($request, $response, $next)
	{
		if (isset($_SESSION['old'])) {
			// Pasa al entorno de Twig/View la variable 'old' con los valores de los campos del form
			$this->container->view->getEnvironment()->addGlobal('old', $_SESSION['old']);
		}		
			// Pasa los valores de los campos del form (post)
			$_SESSION['old'] = $request->getParams();

		// Obligatorio de la interfase Middleware
		$response = $next($request, $response);

		return $response;
	}
}
