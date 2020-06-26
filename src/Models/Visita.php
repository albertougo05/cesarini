<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


/**
 * Visita
 */
class Visita extends Model
{
	// Nombre de la tabla
	protected $table = 'Visitas';
	// Lista de campos modificables:
	protected $fillable = ['IdGuiaReparto', 
						   'IdEmpleado', 
						   'Fecha',
						   'HoraSalida',
						   'HoraRetorno',
						   'Pendiente',
						   'Observaciones' ];

	// Cancelo el registro en campos 'created_at' y 'updated_at' por defecto de Laravel
	public $timestamps = false;

	/**
	 * Relación One To One con Guia de Reparto
	 * 
	 * @return object Objeto de la relacion
	 */
	public function GuiaReparto()
	{

		return $this->hasOne('App\Models\GuiaReparto', 'Id', 'IdGuiaReparto');
	}


	/**
	 * Relación One To One con Empleado
	 * 
	 * @return object Objeto de la relacion
	 */
	public function Empleado()
	{

		return $this->hasOne('App\Models\Empleado', 'Id', 'IdEmpleado');
	}
}