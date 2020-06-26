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
	 * 
	 * @param  Request $request  
	 * @param  Response $response
	 * 
	 * @return vista Pantalla para buscar guia de reparto
	 */
	public function buscarGuiaDeReparto($request, $response)
	{
		$listaGuiaRep = $this->listaParaBuscarGuiaRep();

		$datos = [ 'titulo'   => 'Cesarini - Buscar Guia Rep.',
			       'guiaReparto' => $listaGuiaRep,
			       'vienede'     => $request->getParam('vienede') ];

		return $this->view->render($response, 'repartos/guiadereparto/buscarGuiaRepartoPant.twig', $datos);
	}

	/**
	 * Busca lista de Guias de Reparto
	 * 
	 * @return array Lista de guias
	 */
	public function listaParaBuscarGuiaRep()
	{
		$listaGuia = [];
		$tablaGuiaRep = GuiaReparto::all();

		foreach ($tablaGuiaRep as $value) {
			$empleado = Empleado::find($value->IdEmpleado);
			$nomEmpleado = $empleado->ApellidoNombre;
			$actividad = Actividad::find($value->IdActividad);
			$descActividad = $actividad->Descripcion;

			$listaGuia[] = [ 'id'        => $value->Id,
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
