<?php 

namespace App\Controllers\Tablas;

use App\Models\Empleado;
use App\Models\Categoria;
use App\Controllers\Controller;
use Respect\Validation\Validator as v;


/**
 * Url: /tablas/empleado
 */
class EmpleadosController extends Controller
{
	
	public function getEmpleado($request, $response)
	{
		$idEmpl = $request->getParam('id');

		if (is_null($idEmpl)) {
			$accion = 'Nuevo';
		} else {
			$accion = 'Modifica';
		}

		$categorias = Categoria::all();
		// Se pueden editar TODOS los empleados (incluso dados de baja)
		$empleados = Empleado::orderBy('ApellidoNombre', 'asc')
							 ->get();

		$datos = array('titulo'     => 'Cesarini - Empleados',
			           'categorias' => $categorias, 
			           'empleados'  => $empleados,
			           'idempl'     => $idEmpl,
			           'accion'     => $accion);

		return $this->view->render($response, 'tablas/empleados.twig', $datos);
	}

	/**
	 * POST de Empleado - Crea un nuevo empleado
	 * 
	 * @param  $request
	 * @param  $response
	 * @return Redirige /tablas/empleado (muestra cartel de usuario creado con éxito)
	 */
	public function postEmpleado($request, $response)
	{
		$validacion = $this->validator->validate($request, [
			'apellidonombre' => v::notEmpty(),
			'categoria' => v::notEmpty(),
			'cuil' => v::notEmpty(),
			'CodPostal' => v::notEmpty(),
			'domicilio' => v::notEmpty(),
			'localidad' => v::notEmpty(),
			'provincia' => v::notEmpty(),
			// Telefono y Celular son opcionales y no tiene validación
			'mail' => v::optional(v::email()),
			'estado' => v::notEmpty()
		]);

		// Si falla la validación redirige a la página del form
		if ($validacion->failed()) {

			return $response->withRedirect($this->router->pathFor('tablas.empleado')."?id=".$_SESSION['old']['idempl']);
		}

		$datos = array( 'ApellidoNombre' => $request->getParam('apellidonombre'),
						'IdCategoria'    => $request->getParam('categoria'),
						'Cuil'           => $request->getParam('cuil'),
						'Domicilio'      => $request->getParam('domicilio'),
						'Localidad'      => $request->getParam('localidad'),
						'Provincia'      => $request->getParam('provincia'),
						'CodPostal'      => $request->getParam('CodPostal'),
						'Telefono'       => $request->getParam('telefono'),
						'Celular'        => $request->getParam('celular'),
						'Mail'           => $request->getParam('mail'),
						'Estado'         => $request->getParam('estado') );

		if (Empleado::where('Id', $request->getParam('idempl'))->first()) {
			// Actualiza
			$empleado = Empleado::where('Id', $request->getParam('idempl'))
			                    ->lockForUpdate()->update($datos);
			$flash = 'Empleado actualizado con éxito !';

		} else {
			// Crea nuevo
			$empleado = Empleado::create($datos);
			$flash = 'Empleado creado con éxito !';
		}

		$this->flash->addMessage('info', $flash);

		return $response->withRedirect($this->router->pathFor('tablas.empleado'));
	}

	/**
	 * Url: /tablas/dataempleado
	 * 
	 * @param  $request
	 * @param  $response 
	 * @return json - Todos los datos del empleado
	 */
	public function dataEmpleado($request, $response)
	{
		$empleado = Empleado::where('Id', $request->getParam('idempl'))->first();

		return $empleado->toJson();
	}

	/**
	 * Elimina empleado
	 * 
	 * @return redirecciona a pagina nueva de empleado
	 */
	public function eliminarEmpleado($request, $response)
	{
		Empleado::where( 'Id', $request->getParam('idempl') )
				->update( ['Estado' => 'Baja'] );

		$this->flash->addMessage('info', "Empleado dado de baja !");

		return $response->withRedirect($this->router->pathFor('tablas.empleado'));
	}

	/**
	 * Devuelve lista de empleados (filtrado por Alta)
	 * (Para uso de todo el sistema)
	 * 
	 * @return array
	 */
	public function listaEmpleadosActivos()
	{
		$empleados = Empleado::select('Id', 'ApellidoNombre')
							 ->where('Estado', 'Alta')
							 ->orderBy('ApellidoNombre', 'asc')
							 ->get();
		return $empleados;
	}


}
