<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


/**
 * Modelo de Tipo de Dispenser
 */
class TipoDispenser extends Model
{
	// Nombre de la tabla
	protected $table = 'TipoDispenser';

	// Lista de campos modificables:
	protected $fillable = [
		'Descripcion'
	];

	// Cancelo el registro en campos 'created_at' y 'updated_at' por defecto de Eloquent
	public $timestamps = false;


}