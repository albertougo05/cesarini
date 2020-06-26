<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


/**
 * Modelo de categorias de empleado
 */
class Categoria extends Model
{
	// Nombre de la tabla
	protected $table = 'Categorias';

	// Lista de campos modificables:
	protected $fillable = [
		'Descripcion'
	];

	// Cancelo el registro en campos 'created_at' y 'updated_at' por defecto de Eloquent
	public $timestamps = false;

}