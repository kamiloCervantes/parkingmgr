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
			$_query = sprintf("INSERT INTO vehiculos(id, tipo_vehiculo, nombre_propietario, tel_propietario, id_propietario) VALUES ('%s', '%s', '%s','%s','%s')",strtoupper($_POST['placa']),$_POST['tipo_vehiculo'],$_POST['propietario'],$_POST['tel_propietario'],$_POST['id_propietario']);
			$_query2 = sprintf("INSERT INTO users_vehiculos(users_id, vehiculos_id) VALUES(%d, '%s')",1,strtoupper($_POST['placa']));
			$result_vehiculos = $this->_conn->execute($_query);
			$result_users = $this->_conn->execute($_query2);
			$this->_conn->close();
			unset($_POST['action']);
		}
	}
	
	public function get()
	{
		if($_POST['action']=='getVehiculo.do')
		{
			if(isset($_POST['placa']))
			{
				$this->_conn->connect();
				$_query = sprintf("SELECT tipo_vehiculo, nombre_propietario, tel_propietario, id_propietario FROM vehiculos where id='%s'",strtoupper($_POST['placa']));
				$result = $this->_conn->execute($_query);
				echo $this->_conn->fetch_json($result);
				$this->_conn->close();
				unset($_POST['action']);
			}
		}
	}
	
}