<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


/**
 * Empleado
 */
class Empleado extends Model
{
	// Nombre de la tabla
	protected $table = 'Empleados';

	// Lista de campos modificables:
	protected $fillable = [
		'ApellidoNombre', 
		'IdCategoria', 
		'Cuil', 
		'Domicilio',
		'Localidad',
		'Provincia',
		'CodPostal',
		'Telefono',
		'Celular',
		'Mail',
		'Estado'
	];

	// Cancelo el registro en campos 'created_at' y 'updated_at' por defecto de Eloquent
	public $timestamps = false;

}