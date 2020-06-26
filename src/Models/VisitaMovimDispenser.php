<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


/**
 * Visita
 */
class VisitaMovimDispenser extends Model
{
	// Nombre de la tabla
	protected $table = 'VisitaMovimDispenser';
	// Lista de campos modificables:
	protected $fillable = [ 'IdVisita', 
						    'IdMovDisp' ];

	// Cancelo el registro en campos 'created_at' y 'updated_at' por defecto de Laravel
	public $timestamps = false;


	/**
	 * Relación One To One con Visita
	 * 
	 * @return object Objeto de la relacion
	 */
	public function Visita()
	{

		return $this->hasOne('App\Models\Visita', 'Id', 'IdVisita');
	}

	/**
	 * Relación One To One con MoviminentoDispenser
	 * 
	 * @return object Objeto de la relacion
	 */
	public function MovDispenser()
	{

		return $this->hasOne('App\Models\MovimientoDispenser', 'Id', 'IdMovDisp');
	}

}