<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


/**
 * Modelo de ProductoClienteReparto
 */
class ProductoClienteReparto extends Model
{
	// Nombre de la tabla
	protected $table = 'ProductoClienteReparto';

	// Lista de campos modificables:
	protected $fillable = [
		'IdReparto',
		'IdCliente', 
		'IdDomicilio',
		'IdProducto', 
		'CantSugerida'
	];

	// Cancelo el registro en campos 'created_at' y 'updated_at' por defecto de Eloquent
	public $timestamps = false;

	/**
	 * Relación One To One con Cliente
	 * 
	 * @return object Objeto de la relacion
	 */
	public function Cliente()
	{

		return $this->hasOne('App\Models\Cliente', 'Id', 'IdCliente');
	}

	/**
	 * Relación One To One con Producto
	 * 
	 * @return object Objeto de la relacion
	 */
	public function Producto()
	{

		return $this->hasOne('App\Models\Producto', 'Id', 'IdProducto');
	}


}