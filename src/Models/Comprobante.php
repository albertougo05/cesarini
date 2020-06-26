<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


/**
 * Modelo de Tipo de Dispenser
 */
class Comprobante extends Model
{
	// Nombre de la tabla
	protected $table = 'Comprobantes';

	// Lista de campos modificables:
	protected $fillable = [
		'Fecha',
		'TipoForm',
		'Tipo',
		'Sucursal',
		'NroComprobante',
		'IdCliente',
		'Firma',
		'Concepto',
		'PeriodoFact',
		'FechaDesde',
		'FechaHasta',
		'IdUsuario',
		'Total'
	];

	// Cancelo el registro en campos 'created_at' y 'updated_at' por defecto de Eloquent
	public $timestamps = false;

}