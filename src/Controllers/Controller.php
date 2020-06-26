<?php

namespace App\Controllers;


/**
 * Clase controladora común a todos los controladores
 */
class Controller
{
	protected $container;	

	public function __construct($container)
	{
		$this->container = $container;
	}	

	/**
	 * Funcion mágica __get, verifica si el contenedor tiene la dependencia pasada como property y devuelve esa dependencia ($view for example)
	 * 
	 */
	public function __get($property)
	{
		if ($this->container->{$property}) {

			return $this->container->{$property};
		}
	}
}