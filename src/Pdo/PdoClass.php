<?php

namespace App\Pdo;

use \PDO;
use App\Controllers\Controller;

/**
 * Clase controladora de PDO
 * 
 */
class PdoClass extends Controller
{
	public function pdoQuery($strQuery)
	{
		$host = $this->container['settings']['db']['host'];
		$db   = $this->container['settings']['db']['database'];
		$user = $this->container['settings']['db']['username'];
		$pass = $this->container['settings']['db']['password'];
		$charset = 'utf8mb4';

		$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
		$options = [
		    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
		    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		    PDO::ATTR_EMULATE_PREPARES   => false,
		];
		try {
		     $pdo = new PDO($dsn, $user, $pass, $options);
		} catch (\PDOException $e) {
		     throw new \PDOException($e->getMessage(), (int)$e->getCode());
		}

		$stmt = $pdo->query($strQuery)->fetchAll(PDO::FETCH_ASSOC);

		return $stmt;
	}
}