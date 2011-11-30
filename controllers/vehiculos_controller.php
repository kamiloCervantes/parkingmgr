<?php

include_once '../models/databaseAdapter.php';

class Vehiculos_Controller
{
	private $_conn;
	
	public function __construct()
	{
		$this->_conn = databaseAdapter::getInstance();
		session_start();
	}
	
	public function add()
	{
		if(isset($_SESSION['id']))
		{
			if($_POST['action']=='addVehiculo.do')
			{
				$this->_conn->connect();
				$_query = sprintf("INSERT INTO vehiculos(id, tipo_vehiculo, nombre_propietario, tel_propietario, id_propietario) VALUES ('%s', '%s', '%s','%s','%s')",strtoupper($_POST['placa']),$_POST['tipo_vehiculo'],$_POST['propietario'],$_POST['tel_propietario'],$_POST['id_propietario']);
				$_query2 = sprintf("INSERT INTO users_vehiculos(users_id, vehiculos_id) VALUES(%d, '%s')",$_SESSION['id'],strtoupper($_POST['placa']));
				$result_vehiculos = $this->_conn->execute($_query);
				$result_users = $this->_conn->execute($_query2);
				$this->_conn->close();
				unset($_POST['action']);
			}
		}
		else
		{
			echo '{ "login": "0" }';
		}
	}
	
	public function get()
	{
		if(isset($_SESSION['id']))
		{
			if($_POST['action']=='getVehiculo.do')
			{
				if(isset($_POST['placa']))
				{
					$this->_conn->connect();
					$_query = sprintf("SELECT tipo_vehiculo, nombre_propietario, tel_propietario, id_propietario 
									FROM vehiculos,users_vehiculos where vehiculos.id=users_vehiculos.vehiculos_id 
									and vehiculos.id='%s' and users_vehiculos.users_id=%d",strtoupper($_POST['placa']),$_SESSION['id']);
					$result = $this->_conn->execute($_query);
					echo $this->_conn->fetch_json($result);
					$this->_conn->close();
					unset($_POST['action']);
				}
			}
		}
		else
		{
			echo '{ "login": "0" }';
		}
	}
	
}