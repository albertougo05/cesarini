<?php

namespace App\Controllers\Repartos;

use App\Models\Actividad;
use App\Models\Empleado;
use App\Models\GuiaReparto;

use App\Controllers\Controller;


/**
 * Url: '/repartos/buscarguiarep
 *
 * Name: 'repartos.buscarguiarep'
 * 
 */
class RepartosBuscarGuiaController extends Controller
{

	/**
	 * Pantalla para seleccionar/buscar Guia de Reparto
	 * Name: repartos.buscarguiarep
	 * 
	 * @param  Request $request  
	 * @param  Response $response
	 * 
	 * @return vista Pantalla para buscar guia de reparto
	 */
	public function buscarGuiaDeReparto($request, $response)
	{
		if ($request->getParam('vienede')) {
			$vienede = $request->getParam('vienede');
		} else {
			$vienede = "buscar";
		}

		$datos = [ 'titulo'   => 'Cesarini - Buscar Guia Rep.',
			       'guiaReparto' => $this->listaParaBuscarGuiaRep($vienede),
			       'vienede'     => $vienede ];

		return $this->view->render($response, 'repartos/guiadereparto/buscarGuiaRepartoPant.twig', $datos);
	}

	/**
	 * Busca lista de Guias de Reparto
	 * 
	 * @return array Lista de guias
	 */
	public function listaParaBuscarGuiaRep($vieneDe)
	{
		$listaGuia = [];

		if ($vieneDe == 'visitas') {
			$tablaGuiaRep = GuiaReparto::where('Estado', 'Vigente')->get();
		} else {
			$tablaGuiaRep = GuiaReparto::all();
		}

		foreach ($tablaGuiaRep as $value) {
			$empleado = Empleado::find($value->IdEmpleado);
			$nomEmpleado = $empleado->ApellidoNombre;
			$actividad = Actividad::find($value->IdActividad);
			$descActividad = $actividad->Descripcion;

			$listaGuia[] = [ 'id'        => $value->Id,
							 'nombre'    => $value->Nombre,
					 		 'dia'       => $value->DiaSemana,
							 'turno'     => $value->Turno,
							 'empleado'  => $nomEmpleado,
							 'actividad' => $descActividad,
							 'salida'    => $value->HoraSalida,
							 'retorno'   => $value->HoraRetorno,
							 'estado'    => $value->Estado ];
		}

		return $listaGuia;
	}

}
