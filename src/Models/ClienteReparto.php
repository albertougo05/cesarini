<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


/**
 * Modelo de ClienteReparto
 */
class ClienteReparto extends Model
{
	// Nombre de la tabla
	protected $table = 'ClienteReparto';

	// Lista de campos modificables:
	protected $fillable = [
		'IdReparto',
		'IdCliente', 
		'IdClienteDomicilio', 
		'OrdenVisita'
	];

	// Cancelo el registro en campos 'created_at' y 'updated_at' por defecto de Eloquent
	public $timestamps = false;

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
	 * Relación One To One con ClienteDomicilio
	 * 
	 * @return object Objeto de la relacion
	 */
	public function ClienteDomicilio()
	{

		return $this->hasOne('App\Models\ClienteDomicilio', 'Id', 'IdClienteDomicilio');
	}

}