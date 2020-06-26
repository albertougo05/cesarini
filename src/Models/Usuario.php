<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


/**
 * Usuario
 */
class Usuario extends Model
{
	// Nombre de la tabla
	protected $table = 'Usuarios';
	// Lista de campos modificables:
	protected $fillable = ['Usuario', 'Contrasena', 'Nivel'];
	// Cancelo el registro en campos 'created_at' y 'updated_at' por defecto de Laravel
	public $timestamps = false;

}