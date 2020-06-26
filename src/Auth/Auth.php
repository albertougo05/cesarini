<?php 

namespace App\Auth;

use App\Models\Usuario;


/**
 * Autorizacion de usuario
 * 
 */
class Auth 
{
	/**
	 * Controla (check) que esté seteado el Id de usuario en la session
	 * 
	 * @return boolean Si existe o no el id de usuario (si se ha logeado)
	 */
	public function check()
	{

		return isset($_SESSION['user']);
	}

	/**
	 * Devuelve en nombre del usuario
	 * 
	 * @return string 
	 */
	public function user()
	{
		if (isset($_SESSION['user'])) {
			$usuario = Usuario::find($_SESSION['user']);
		} else
		    $usuario = null;

		return $usuario;
	}

	/**
	 * Autorizacion para cara intento (attempt)
	 * 
	 * @param  string $usuario [description]
	 * @param  string $passw   [description]
	 * 
	 * @return [type]          [description]
	 */
	public function attempt($usuario, $passw)
	{
		$user = Usuario::where('Usuario', $usuario)->first();

		if (!$user) {    # Si no se encuentra el usuario...

			return false;
		}

		if (password_verify($passw, $user->Contrasena)) {    # Si la contraseña es correcta...

			// Set variable 'user' para validar todas las páginas
			$_SESSION['user'] = $user->Id;

			return true;
		}

		return false;
	}

	/**
	 * Reset variable de session 'user'
	 * 
	 */
	public function logout()
	{
		unset($_SESSION['user']);
	}

}
