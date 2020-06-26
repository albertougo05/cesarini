<?php

namespace App\Middleware;

/**
 * 
 */
class ValidationErrorsMiddleware extends Middleware
{

	public function __invoke($request, $response, $next)
	{
		if (isset($_SESSION['errors'])) {
			// Pasa al entorno de Twig/View la variable 'errors' 
			$this->container->view->getEnvironment()->addGlobal('errors', $_SESSION['errors']);

			unset($_SESSION['errors']);			
		}

		$response = $next($request, $response);

		return $response;
	}
}
