<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


/**
 * Modelo de tipo de producto
 */
class TipoFacturacion extends Model
{
	// Nombre de la tabla
	protected $table = 'TiposFacturacion';

	// Lista de campos modificables:
	protected $fillable = [
		'Descripcion',
        'MontoAbono'
	];

	// Cancelo el registro en campos 'created_at' y 'updated_at' por defecto de Eloquent
	//public $timestamps = false;

}