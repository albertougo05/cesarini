<?php 

namespace App\Controllers\Tablas;

use App\Models\Usuario;
use App\Controllers\Controller;
use Respect\Validation\Validator as v;


/**
 * Url: '/tablas/usuarios
 */
class UsuariosController extends Controller
{
	/**
	 * GET /tablas/usuarios
	 * 
	 * @param  [type] $request  [description]
	 * @param  [type] $response [description]
	 * @return vista datos usuario
	 */
	public function getUsuario($request, $response)
	{
		// Lista de usuarios para select de busqueda
		$usuarios = Usuario::where('Id', '>', 0)->orderBy('Usuario')->get();
		$idUser = $request->getParam('iduser');

		if (is_null($idUser)) {
			$accion = 'Nuevo';
		} else $accion = 'Modifica';

		$datos = array(
			'titulo' => 'Cesarini - Usuarios',
			'accion' => $accion,
			'usuarios' => $usuarios,
	    );

		return $this->view->render($response, 'tablas/usuarios.twig', $datos);
	}

	/**
	 * POST de Usuario - Crea un nuevo usuario
	 * 
	 * @param  $request
	 * @param  $response
	 * @return Redirige /tablas/usuarios (muestra cartel de usuario creado con éxito)
	 */
	public function postUsuario($request, $response)
	{
		// Si se creó el usuario borro los datos old
		if (isset($_SESSION['old'])) {
			$_SESSION['old'] = [];
		}

		// Si es modificacion...
		if ($request->getParam('iduser') != null) {
			$this->_actualizaUsuario($request);

			return $response->withRedirect($this->router->pathFor('tablas.usuarios'));
		}

		$usuario = Usuario::create([
			'Usuario'    => $request->getParam('usuario'),
			'Contrasena' => password_hash($request->getParam('contrasena'), PASSWORD_DEFAULT),
			'Nivel'      => $request->getParam('nivel'),
		]);

		$this->flash->addMessage('info', 'Usuario creado con éxito !');

		return $response->withRedirect($this->router->pathFor('tablas.usuarios'));
	}

	/**
	 * GET: /tablas/usuario?id=xx
	 * Name: tablas.datausuario
	 * 
	 * @param  $request
	 * @param  $response 
	 * 
	 * @return json con datos del usuario (usado por ajax)
	 */
	public function dataUsuario($request, $response)
	{
		$usuario = Usuario::where('Id', $request->getParam('iduser'))->first();

		$_SESSION['old']['id']         = $usuario->Id;
		$_SESSION['old']['usuario']    = $usuario->Usuario;
		$_SESSION['old']['contrasena'] = $usuario->Contrasena;
		$_SESSION['old']['confirma']   = $usuario->Contrasena;
		$_SESSION['old']['nivel']      = $usuario->Nivel;

		return $response->withRedirect($this->router->pathFor('tablas.usuarios')."?iduser=".$usuario->Id);
	}

	/**
	 * Verifica si el nombre de usuario existe en la base de datos
	 * Name: tablas.usuariodisponible
	 * 
	 * @param $request 
	 * @param $response
	 */
	public function usuarioDisponible($request, $response)
	{
		$input = $request->getParam('nombreUsuario');

		# Retorna true si NO existe (count es 0)
		return json_encode( [ 'existe' => Usuario::where('Usuario', $input)->count() === 1] );
	}

	/**
	 * Actualiza datos de usuario cuando es edición de datos.
	 * 
	 * @param  Request $req 
	 * @return null
	 */
	private function _actualizaUsuario($req)
	{
		$usuario = $req->getParam('usuario');
		// Si la contraseña es mas corta (no está cifrada)
		if (strlen($req->getParam('contrasena')) < 59) {
			$newPass = password_hash($req->getParam('contrasena'), PASSWORD_DEFAULT);
		} else {
			$newPass = $req->getParam('contrasena');
		}

		$contrasena = $newPass;
		$nivel = $req->getParam('nivel');

		$user = Usuario::where('Id', $req->getParam('iduser'))
						->lockForUpdate()
						->update(['Usuario'   => $usuario,
								 'Contrasena' => $contrasena,
								 'Nivel'      => $nivel]);

		$this->flash->addMessage('info', 'Usuario actualizado !');

		return null;
	}

	/**
	 * Eliminar Usuario
	 * 
	 * @param  Reques $request 
	 * @param  Response $response 
	 * @return [type]           [description]
	 */
	public function eliminarUsuario($request, $response)
	{
		$id = $request->getParam('iduser');

		Usuario::where('Id', $id)->delete();

		$this->flash->addMessage('info', "Usuario eliminado !");

		return $response->withRedirect($this->router->pathFor('tablas.usuarios'));
	}

}
