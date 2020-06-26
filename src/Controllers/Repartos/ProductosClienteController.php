<?php

namespace App\Controllers\Repartos;

use App\Models\Cliente;
use App\Models\ClienteDomicilio;
use App\Models\VisitaSalidaProducto;
use App\Models\VisitaDetalleCliente;

use App\Controllers\Controller;


/**
 * Clase VisitasListadoController
 * 
 * Url base: '/repartos/productoscliente'
 * 
 */
class ProductosClienteController extends Controller
{

	/**
	 * Productos de cliente
	 * Name: 'repartos.productoscliente'
	 * 
	 * @param  Request  $request
	 * @param  Response $response
	 * @return view
	 */
	public function prodsCliente($request, $response)
	{
		$datos = array( 'titulo'     => 'Cesarini - Productos cliente',
			            'accion'     => 'Nuevo' );

		return $this->view->render($response, 'repartos/prodsclie/prodsclie.twig', $datos);
	}

	/**
	 * Listado Productos de cliente
	 * Name: 'repartos.productoscliente.listado'
	 * 
	 * @param  Request  $request
	 *         (?idcli=9&iddom=8&todosdom=true&clisNoGR=false&ordGuiRe=true&ordDia=false&ordTurno=false)
	 * @param  Response $response
	 * @return view
	 */
	public function listado($request, $response)
	{
	    date_default_timezone_set("America/Buenos_Aires");

		$datos = [ 'titulo' => 'Cesarini - Prods. Cliente',
				   'fecha'  => date('d/m/Y'), ];

	    if ($request->getParam('clisNoGR') == 'true') {
	    	# Ver clientes que NO están en GRs
	    	$lista = $this->_cliesNoEnGuiaRep($request);
	    	$datos['titulo'] = "Clientes NO en G.R.";
	    	$datos['listado'] = $lista;

    		return $this->view->render($response, 'repartos/prodsclie/imprimeClieNoEnGR.twig', $datos);

	    } else {
	    	# Productos de cliente en Guías de reparto

	    	# Buscar GR
	    	$guiasRep = $this->_guiasRepartoDelClie($request);

	    	$datos['guiasRep'] = $guiasRep;

			return $this->view->render($response, 'repartos/prodsclie/imprimelist.twig', $datos);
		}
	}

	/**
	 * Busca GR del cliente
	 * 
	 * @param  Request $req 
	 * @return array
	 */
	private function _guiasRepartoDelClie($req)
	{
		$sql = $this->_sqlGuiasRepCli($req);
		$guiasRep = $this->pdo->pdoQuery($sql);

	    # Ingreso cada array de productos en array clientes
	    foreach ($guiasRep as $key => $value) {
	    	$guiasRep[$key]['productos'] = $this->_productosGR($value, $req->getParam('todosdom'));
	    }

		return $guiasRep;
	}

	/**
	 * Arma sql de guias rep cliente
	 * 
	 * @param  Request $req 
	 * @return array
	 */
	private function _sqlGuiasRepCli($req)
	{
		$andFlag = false;  // Para saber si ya hay un 'order by'

		$sql = "SELECT cr.IdReparto, gr.DiaSemana, gr.Turno, ";
		$sql = $sql . "gr.IdEmpleado, em.ApellidoNombre AS Repartidor, ";
		$sql = $sql . "cr.IdCliente, cr.IdClienteDomicilio, cr.OrdenVisita, ";
		$sql = $sql . "cl.ApellidoNombre, cd.Direccion FROM ClienteReparto AS cr ";
		$sql = $sql . "LEFT JOIN Clientes AS cl ON cr.IdCliente = cl.Id ";
		$sql = $sql . "LEFT JOIN ClientesDomicilio AS cd ON cr.IdClienteDomicilio = cd.Id ";
		$sql = $sql . "LEFT JOIN GuiaRepartos AS gr ON cr.IdReparto = gr.Id ";
		$sql = $sql . "LEFT JOIN Empleados AS em ON gr.IdEmpleado = em.Id ";
		$sql = $sql . "WHERE cr.IdCliente = " . $req->getParam('idcli') . " ";

		if ($req->getParam('todosdom') == 'false') {
			$sql = $sql . "AND cr.IdClienteDomicilio = " . $req->getParam('iddom') . " ";
		} else {
			$sql = $sql . "ORDER BY cr.IdClienteDomicilio ";
			$andFlag = true;
		}

		if ($andFlag && $req->getParam('ordGuiRe') == 'true') {
			$sql = $sql . "AND cr.IdReparto";
		} else if ($req->getParam('ordGuiRe') == 'true') {
			$sql = $sql . "ORDER BY cr.IdReparto ";
			$andFlag = true;
		}

		if ($andFlag && $req->getParam('ordDia') == 'true') {
			$sql = $sql . "AND gr.DiaSemana ";
		} else if ($req->getParam('ordDia') == 'true') {
			$sql = $sql . "ORDER BY gr.DiaSemana ";
			$andFlag = true;
		}

		if ($andFlag && $req->getParam('ordTurno') == 'true') {
			$sql = $sql . "AND gr.Turno ";
		} else if ($req->getParam('ordTurno') == 'true') {
			$sql = $sql . "ORDER BY gr.Turno ";
		}

		return $sql;
	}

	/**
	 * Busca productos de cada una de las guias de reparto
	 * 
	 * @param  array $guiasRep
	 * @return array
	 */
	private function _productosGR($guiaRep, $todosdom)
	{
		$sql = '';
		$sql1 = $this->_sqlProdsGR();
		$sql2 = "AND pc.IdDomicilio = ";

		//foreach ($guiasRep as $value) {

			$sql = $sql1 . $guiaRep['IdCliente']." ";

			if ($todosdom = 'true') {
				$sql = $sql . "AND pc.IdDomicilio = " . $guiaRep['IdClienteDomicilio'] . " ";
			}

			$sql = $sql . "AND pc.IdReparto = " . $guiaRep['IdReparto'] . " ";
			$sql = $sql . "ORDER BY pc.IdProducto;";

			$prods = $this->pdo->pdoQuery($sql);
		//}

		return $prods;
	}

	/**
	 * Arma slq para productos cliente
	 * 
	 * @return [type] [description]
	 */
	private function _sqlProdsGR()
	{
		$sql = "SELECT pc.IdReparto, pc.IdCliente, pc.IdDomicilio, ";
		$sql = $sql . "pc.IdProducto, pr.Descripcion, pc.CantSugerida ";
		$sql = $sql . "FROM ProductoClienteReparto AS pc ";
		$sql = $sql . "LEFT JOIN Productos AS pr ON pc.IdProducto = pr.Id ";
		$sql = $sql . "WHERE pc.IdCliente = ";

		return $sql;
	}

	/**
	 * Devuelve lista de clientes que NO están en una GR
	 * 
	 * @return [type] [description]
	 */
	private function _cliesNoEnGuiaRep()
	{
		$sql = "SELECT * FROM Clientes WHERE Id ";
		$sql = $sql . "NOT IN (SELECT DISTINCT IdCliente ";
		$sql = $sql ."FROM ClienteReparto ORDER BY IdCliente) ";
    	$sql = $sql . "ORDER BY ApellidoNombre";
    	$listado = $this->pdo->pdoQuery($sql);

		return $listado;
	}

}
