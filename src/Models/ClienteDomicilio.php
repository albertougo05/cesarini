<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


/**
 * Modelo de ClienteDomicilio
 */
class ClienteDomicilio extends Model
{
	// Nombre de la tabla
	protected $table = 'ClientesDomicilio';

	// Lista de campos modificables:
	protected $fillable = [
		'IdCliente', 
		'Direccion', 
		'Localidad',
		'Provincia',
		'CodPostal',
		'Telefono',
		'Celular',
		'Contacto'
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

}