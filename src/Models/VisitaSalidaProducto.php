<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


/**
 * Visita
 */
class VisitaSalidaProducto extends Model
{
	// Nombre de la tabla
	protected $table = 'VisitaSalidaProductos';
	// Lista de campos modificables:
	protected $fillable = ['IdVisita', 
						   'IdProducto', 
						   'CantRetirado',
						   'CantDevuelto',
						   'EnvasesDevueltos' ];
	// Cancelo el registro en campos 'created_at' y 'updated_at' por defecto de Laravel
	public $timestamps = false;


	/**
	 * Relación One To One con Visita
	 * 
	 * @return object Objeto de la relacion
	 */
	public function Visita()
	{

		return $this->hasOne('App\Models\Visita', 'Id', 'IdVisita');
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