<?php

namespace App\Controllers\Repartos;


use App\Models\Empleado;

use App\Controllers\Controller;


/**
 *
 * Clase VisitasInfoResumController
 * 
 */
class VisitasInfoResumController extends Controller
{
	/**
	 * Resumen Resumido de Visitas (Pantalla principal)
	 * Name: 'repartos.visitasinforesum'
	 * 
	 * @param  Request  $request
	 * @param  Response $response
	 * @return view
	 */
	public function informe($request, $response)
	{
	    $fecha_actual = $this->utils->getFechaActual("Y-m-d");
	    $empleados = $this->EmpleadosController->listaEmpleadosActivos();

		$datos = array('titulo'     => 'Cesarini - Visitas Info Resumido', 
					   'empleados'  => $empleados,
					   'fechaDesde' => date("Y-m-d",strtotime($fecha_actual."- 1 month")),
					   'fechaHasta' => date('Y-m-d') );

		return $this->view->render($response, 'repartos/visitas/visitasInfoResumido.twig', $datos);
	}





}
