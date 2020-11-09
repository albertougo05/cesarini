<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


/**
 * Modelo de Cliente
 */
class Cliente extends Model
{
	// Nombre de la tabla
	protected $table = 'Clientes';

	// Lista de campos modificables:
	protected $fillable = [
		'ApellidoNombre', 
		'NombreFantasia',
		'Direccion', 
		'Localidad',
		'Provincia',
		'CodPostal',
		'Telefono',
		'Celular',
		'CUIT',
		'CondicionFiscal',
		'Email',
		'Estado',
		'NroContrato',
		'FechaVencContrato',
		'FechaAltaServicio',
		'FechaFacturacion',
		'CostoAbono',
		'Observaciones'
	];

	// Cancelo el registro en campos 'created_at' y 'updated_at' por defecto de Eloquent
	public $timestamps = false;

	/**
	 * Relación One To Many con ClienteDomicilio
	 * 
	 * @return object Objeto de la relacion
	 */
	public function Domicilios()
	{

		return $this->hasMany('App\Models\ClienteDomicilio', 'IdCliente', 'Id');
	}

}