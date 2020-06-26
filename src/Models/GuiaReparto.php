<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


/**
 * Modelo de GuiaReparto
 */
class GuiaReparto extends Model
{
	// Nombre de la tabla
	protected $table = 'GuiaRepartos';
	// Lista de campos modificables:
	protected $fillable = [
		'DiaSemana', 
		'Turno', 
		'IdEmpleado',
		'IdActividad',
		'HoraSalida',
		'HoraRetorno',
		'Estado'
	];
	// Cancelo el registro en campos 'created_at' y 'updated_at' por defecto de Eloquent
	public $timestamps = false;


	/**
	 * Relación One To One con Empleado
	 * 
	 * @return object Objeto de la relacion
	 */
	public function Empleado()
	{

		return $this->hasOne('App\Models\Empleado', 'Id', 'IdEmpleado');
	}

	/**
	 * Relación One To One con Actividad
	 * 
	 * @return object Objeto de la relacion
	 */
	public function Actividad()
	{

		return $this->hasOne('App\Models\Actividad', 'Id', 'IdActividad');
	}



}