<?php

namespace App\Controllers\Repartos;

use App\Models\Cliente;
use App\Models\Producto;
use App\Models\TipoProducto;
use App\Models\ProductoClienteReparto;

use App\Controllers\Controller;


/**
 * Url: '/repartos/productosacliente'
 * 
 * Name: 'repartos.productosacliente'
 * 
 */
class RepartosProductosaClieController extends Controller
{

	/**
	 * Pantalla para agregar productos a cliente
	 * Name: 'repartos.productosacliente'
	 * 
	 * @param  $request  [description]
	 * @param  $response [description]
	 * @return view
	 */
	public function productosACliente($request, $response)
	{
		$id = $this->_idCliente( $request->getParam('idclie'), $request->getParam('iddom') );
		$idGuiaRep = $_SESSION['idguiarep']; //$request->getParam('idguiarep');

		// Buscar los productos del cliente
		$productosClie = $this->_buscarProductosCliente( $request->getParam('idclie'), $request->getParam('iddom') );
		// Busca lista de productos para select
		$productos = $this->_armaListaDeProductos();
		// Armo array con lista para mostrar en pantalla: cant + descripcion
		$listaProductosClie = $this->_armaListaProductosClie($productos, $productosClie);


//echo "<br><pre>";
//print_r($productosClie);
//print_r($_SESSION['cliProductos']);
//echo "<br><br>";
//print_r($_SESSION['listaClie']);
//die('Ver...');

		$datos = [ 'titulo'       => 'Cesarini - Productos a Cliente',
				   'productosCli' => $listaProductosClie,
				   'accion'       => 'Agregar Productos',
				   'id'           => $id,
				   'idclie'       => $request->getParam('idclie'),
				   'iddom'		  => $request->getParam('iddom'),
				   'cliente'      => $request->getParam('nomclie'),
				   'idGuia'       => $idGuiaRep,
				   'productos'    => $productos ];

		$this->view->render($response, 'repartos/guiadereparto/productos_a_cliente.twig', $datos);
	}

	/**
	 * Busca lista de productos para presentar en pantalla
	 * 
	 * @return array [description]
	 */
	private function _buscarProductosCliente($idClie, $idDom)
	{
		$productosCliente = [];

		// si está vacia $_SESSION['cliProductos'], buscar en disco...
		if (empty($_SESSION['cliProductos'])) {

			// Para primer ingreso...
			return false;

		} else { 

			// La clave con el id del cliente
			$id = $this->_idCliente( $idClie, $idDom );

//			if (array_key_exists($idClie, $_SESSION['cliProductos'])) {
			if ( array_key_exists( $id, $_SESSION['cliProductos']) ) {

				$prodsClie = $_SESSION['cliProductos'][$id];  // cada key lo cargo al array de la lista de productos
																 // para llenar cada input en form ingreso productos a cliente
				foreach ($prodsClie as $key => $value) {

					$productosCliente[$key] = $value;
				}
			}
		}

		return $productosCliente;
	}

	/**
	 * Url: '/repartos/productosacliente' (POST)
	 * Name: 'repartos.productosacliente'
	 *
	 * Valida y registra la entrada de datos desde pantalla ingreso productos a cliente
	 * Crea un array con Id de cliente (id) con un array con idProducto => cantidad
	 * 
	 * $_SESSION['cliProductos'][$id]
	 * array(1) { [3]=> array(3) { ["id15tp4"]=> string(1) "1" ["id16tp4"]=> string(1) "1" ["id17tp4"]=> string(1) "1" } } 
	 */
	public function postProductosACliente($request, $response)
	{
		$id     = $request->getParam('id');
		$idClie = $request->getParam('idclie');
		$idDom  = $request->getParam('iddom');
		$nomClie = $request->getParam('nomclie');
		$cantidad = $request->getParam('cantProduct');
		$idProduc = $request->getParam('selectProducto');
	
		$_SESSION['cliProductos'][$id][$idProduc] = $cantidad;
		$_SESSION['guiaModificada'] = true;

		return $response->withRedirect($this->router->pathFor('repartos.productosacliente')."?idclie=".$idClie."&nomclie=".$nomClie."&iddom=".$idDom);
	}

	/**
	 * Modifica cantidad de producto del cliente (viene del modal que modifica cantidad)
	 * Name: 'repartos.modifcantproducto'
	 * 
	 * @param  Request $request 
	 * @param  Response $response
	 * @return redirecciona a página de productos del cliente
	 */
	public function modifcantproducto($request, $response)
	{
		$id       = $request->getParam('id');
		$idClie   = $request->getParam('idclie');
		$nomClie  = $request->getParam('nomclie');
		$idDom    = $request->getParam('iddom');
		$cantidad = $request->getParam('cant');
		$idProduc = $request->getParam('idprod');

		//if ($cantidad == 0) {
			# eliminar el id del producto del array
		//	unset($_SESSION['cliProductos'][$id][$idProduc]);
		//} else {

		$_SESSION['cliProductos'][$id][$idProduc] = $cantidad;			

//echo "Id: ".$id." - Id prod: ".$idProduc."<br><pre>";
//print_r($_SESSION['cliProductos']);
//echo "<br>";
//die('Ver cliProductos');

		//}

		return $response->withRedirect($this->router->pathFor('repartos.productosacliente')."?idclie=".$idClie."&nomclie=".$nomClie."&iddom=".$idDom);
	}

	/**
	 * Arma la lista de productos y tipos de productos
	 * 
	 * @return array Listado de tipoProducto + producto
	 */
	private function _armaListaDeProductos()
	{
		$tiposProducto = TipoProducto::all();
		$productos     = [];
		$listaProduct  = [];

		foreach ($tiposProducto as $tipoProd) {
			$strDescTP = $tipoProd->Descripcion;

			$productos = Producto::where('IdTipoProducto', $tipoProd->Id)->get();
			foreach ($productos as $prod) {
				// "id01tp1"
				$index = "id".sprintf("%'.02d", $prod->Id)."tp".$tipoProd->Id;
				$listaProduct[$index] = $strDescTP." - ".$prod->Descripcion;
			}
		}

		//print_r($listaProduct);
		//die();
		// Array ( [id01tp1] => Agua Desmineralizada - Bidón x 10 [id02tp1] => Agua Desmineralizada - Vacio x 10 [id03tp1] => Agua Desmineralizada - Bidón x 5 [id04tp1] => Agua Desmineralizada - Vacio x 5 [id05tp1] => Agua Desmineralizada - Bidón x 1 [id06tp1] => Agua Desmineralizada - A granel [id07tp2] => Agua Mineralizada - Bidón x 20 [id08tp2] => Agua Mineralizada - Bidón x 12 [id09tp3] => Hielo - Bolsa x 12 [id10tp3] => Hielo - Bolsa x 3 [id11tp3] => Hielo - Bolsa x 1.5 [id12tp3] => Hielo - Molido x 12 [id13tp3] => Hielo - Barra [id14tp3] => Hielo - 1/2 Barra [id15tp4] => Soda - Sifón x 1,5 [id16tp4] => Soda - Sifón x 1,25 [id17tp4] => Soda - Sifón x 1 [id18tp4] => Soda - Sifón x 0,650 ) 

		return $listaProduct;
	}

	/**
	 * Arma lista de productos para presentar en pantalla
	 * 
	 * @param  array $productos  [Lista de productos]
	 * @param  array $cantidades [Cantidades de cada producto]
	 * @return array            
	 */
	private function _armaListaProductosClie($productos, $cantidades)
	{
		// Productos del cliente (cantidades):
		//  -> Array ( [id01tp1] => 1 [id02tp1] => 1 )
		$lista = [];

		if ($cantidades) {
		
			foreach ($cantidades as $key => $value) {
				$codigoProd = mb_substr($key, 2, 2);
				$lista[] = array('codprod1' => $key, 
								 'codprod2' => $codigoProd, 
					             'descrip' => $productos[$key],
					             'cantidad' => $value
					            );
			}
		}

		return $lista;
	}

	/**
	 * Saca id del cliente de $_SESSION['listaClie']
	 * 
	 * @param  int $idcli
	 * @param  int $iddom
	 * @return int
	 */
	private function _idCliente($idcli, $iddom)
	{
		// Si existe la clave con el id del cliente
		foreach ($_SESSION['listaClie'] as $valor) {

			if ( $valor['idCliente'] == $idcli && $valor['idDomicilio'] == $iddom ) {
				$id = $valor['id'];
			}
		}

		return $id;
	}

}
