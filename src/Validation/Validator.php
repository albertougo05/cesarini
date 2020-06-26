<?php

namespace App\Validation;

use Respect\Validation\Validator as Respect;
use Respect\Validation\Exceptions\NestedValidationException;

/**
 * 
 */
class Validator
{
	protected $errors;

	/**
	 * Valida los campos según las reglas pasadas en el array $rules
	 * 
	 * @param  Request $request [description]
	 * @param  array  $rules   [description]
	 * @return object Esta clase
	 */
	public function validate($request, array $rules)
	{
		foreach ($rules as $field => $rule) {

			try {
				// Pone en mayuscula primera letra del mensaje, que es el nombre del campo
				$rule->setName(ucfirst($field))->assert($request->getParam($field));

			} catch (NestedValidationException $e) {

				$this->errors[$field] = $e->getMessages();
			}
		}

		// Los errores van a ser pasados al entorno de Twig en ValidationErrorsMiddleware
		$_SESSION['errors'] = $this->errors;

		return $this;
	}	

	/**
	 * Si la validacion falla, devuelve el array con los errores
	 * 
	 * @return array Lista de errores
	 */
	public function failed()
	{

		return !empty($this->errors);
	}

	// Authentication with Slim 3 - Showing errors... (12/29) 0:00

}