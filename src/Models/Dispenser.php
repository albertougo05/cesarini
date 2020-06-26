<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


/**
 * Modelo de Dispenser
 */
class Dispenser extends Model
{
	// Nombre de la tabla
	protected $table = 'Dispenser';

	// Lista de campos modificables:
	protected $fillable = [
		'NroSerie',
		'NroInterno',
		'Modelo',
		'IdTipo',
		'FechaAlta',
		'FechaUtlService',
		'FechaBaja',
		'Estado'
	];

	// Cancelo el registro en campos 'created_at' y 'updated_at' por defecto de Eloquent
	public $timestamps = false;

	/**
	 * Relación One To One con TipoDispenser
	 * 
	 * @return object Objeto de la relacion
	 */
	public function DescTipo()
	{

		return $this->hasOne('App\Models\TipoDispenser', 'Id', 'IdTipo');
	}

}