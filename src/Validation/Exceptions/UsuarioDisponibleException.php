<?php

namespace App\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

/**
 * Para mostrar mensajes de exceccion
 * 
 */
class UsuarioDisponibleException extends ValidationException
{
	public static $defaultTemplates = [
		self::MODE_DEFAULT => [
			self::STANDARD => 'El usuario ya existe en la base de datos',
		],
	];

}
