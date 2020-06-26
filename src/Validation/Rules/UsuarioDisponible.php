<?php

namespace App\Validation\Rules;

use App\Models\Usuario;
use Respect\Validation\Rules\AbstractRule;

/**
 * Para validar si existe el usuario en la BD
 * 
 */
class UsuarioDisponible extends AbstractRule
{
	
	public function validate($input)
	{
		//return false;    # Retorna true si NO existe (count es 0)
		return Usuario::where('Usuario', $input)->count() === 0;
	}

}