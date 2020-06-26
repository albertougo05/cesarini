<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


/**
 * Modelo de Producto
 */
class Producto extends Model
{
	// Nombre de la tabla
	protected $table = 'Productos';

	// Lista de campos modificables:
	protected $fillable = [
		'IdTipoProducto',
		'Descripcion',
		'Presentacion',
		'Precio',
		'PrecioExcedente',
		'ConStock'
	];

	// Cancelo el registro en campos 'created_at' y 'updated_at' por defecto de Eloquent
	public $timestamps = false;

	/**
	 * Relación One To One con TipoProducto
	 * 
	 * @return object Objeto de la relacion
	 */
	public function DescripTipoProducto()
	{

		return $this->hasOne('App\Models\TipoProducto', 'Id', 'IdTipoProducto');
	}

}