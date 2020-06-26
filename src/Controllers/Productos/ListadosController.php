<?php 

namespace App\Controllers\Productos;

use App\Models\Producto;
use App\Models\TipoProducto;
use App\Controllers\Controller;


/**
 * Url: /productos/listados
 * 
 */
class ListadosController extends Controller
{
	/**
	 * Listado alfabético
	 * Url: /productos/listados
	 * 
	 * @param  Request $request
	 * @param  Response $response
	 * @return View Listado alfabético
	 */
	public function listados($request, $response)
	{
		$tipoList = $request->getParam('list');

		if (is_null($tipoList) || $tipoList == 1) {
			$accion = 'Alfabético';
			$urlParam = '?list=1';
			$listado = $this->_listaAlfa();
		} else {
			$accion = 'por Tipo Producto';
			$urlParam = '?list=2';
			$listado = $this->_listaTipo();
		}

		$datos = [ 'titulo'   => 'Cesarini - Listados',
				   'accion'   => $accion,
				   'urlParam' => $urlParam,
				   'listado'  => $listado
		];

		return $this->view->render($response, 'productos/listados.twig', $datos);
	}

	/**
	 * Imprime Listado de productos
	 * Url: /productos/imprimir 
	 * (Name: productos.imprimir)
	 * 
	 */
	public function imprimir($request, $response)
	{
		$tipoList = $request->getParam('list');

		if ($tipoList == 1) {
			$accion = 'Alfabético';
			$listado = $this->_listaAlfa();
			$urlParam = '?list=1';
		} else {
			$accion = 'por Tipo Producto';
			$listado = $this->_listaTipo();
			$urlParam = '?list=2';
		}

		$datos = [ 'titulo'   => 'Cesarini - Imprimir',
				   'accion'   => $accion,
				   'urlParam' => $urlParam,
				   'listado'  => $listado
		];

		return $this->view->render($response, 'productos/imprimir.twig', $datos);
	}

	/**
	 * Devuelve array con lista alfabética
	 * 
	 * @return Array 
	 */
	private function _listaAlfa()
	{

		return Producto::orderBy('Descripcion', 'asc')->get();  # Trae todos los productos
	}

	/**
	 * Devuelve array con lista alfabética
	 * 
	 * @return Array 
	 */
	private function _listaTipo()
	{

		return Producto::orderBy('IdTipoProducto', 'asc')->orderBy('Descripcion', 'asc')->get();  # Trae todos los productos
	}


}
