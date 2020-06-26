<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


/**
 * Modelo de MovimientosDispenser
 */
class MovimientoDispenser extends Model
{
	// Nombre de la tabla
	protected $table = 'MovimientosDispenser';

	// Lista de campos modificables:
	protected $fillable = [
		'IdDispenser',
		'Fecha',
		'IdEmpleado',
		'IdCliente', 
		'IdDomicilio',
		'Observaciones',
		'Estado'
	];

	// Cancelo el registro en campos 'created_at' y 'updated_at' por defecto de Eloquent
	public $timestamps = false;

	/**
	 * Relación One To One con Dispenser
	 * 
	 * @return object Objeto de la relacion
	 */
	public function Dispenser()
	{

		return $this->hasOne('App\Models\Dispenser', 'Id', 'IdDispenser');
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
	 * Relación One To One con ClientesDomicilio
	 * 
	 * @return object Objeto de la relacion
	 */
	public function ClienteDomicilio()
	{

		return $this->hasOne('App\Models\ClienteDomicilio', 'Id', 'IdDomicilio');
	}

}