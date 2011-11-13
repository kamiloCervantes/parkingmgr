<?php

include_once '../models/databaseAdapter.php';

class Vehiculos_Controller
{
	private $_conn;
	
	public function __construct()
	{
		$this->_conn = databaseAdapter::getInstance();
	}
	
	
	public function add()
	{
		if($_POST['action']=='addVehiculo.do')
		{
			$this->_conn->connect();
			$_query = sprintf("INSERT INTO vehiculos(id, tipo_vehiculo, nombre_propietario, tel_propietario, id_propietario) VALUES ('%s', '%s', '%s','%s','%s')",$_POST['placa'],$_POST['tipo_vehiculo'],$_POST['propietario'],$_POST['tel_propietario'],$_POST['id_propietario']);
			//$_query = "INSERT INTO vehiculos(id, tipo_vehiculo, nombre_propietario, tel_propietario, id_propietario) VALUES ('".$_POST['placa']."', '".$_POST['tipo_vehiculo']."', 'kamilo', '7900484', '1067873268')";
			$this->_conn->execute($_query);
			$this->_conn->close();
			echo '{ "respuesta" : "200"}';
		}
	}
	
}