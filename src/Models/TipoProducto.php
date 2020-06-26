<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


/**
 * Modelo de tipo de producto
 */
class TipoProducto extends Model
{
	// Nombre de la tabla
	protected $table = 'TipoProducto';

	// Lista de campos modificables:
	protected $fillable = [
		'Descripcion'
	];

	// Cancelo el registro en campos 'created_at' y 'updated_at' por defecto de Eloquent
	public $timestamps = false;

    /**
     * Obtengo los datos de Productos
     *
     * Relación OneToMany (1 TipoProducto => Muchos Productos)
     */
    public function productos()
    {
        return $this->hasMany('App\Models\Producto', 'IdTipoProducto', 'Id');
    }

}