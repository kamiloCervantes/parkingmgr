<?php

include_once '../models/databaseAdapter.php';

class Servicios_Controller
{
	private $_conn;
	
	public function __construct()
	{
		$this->_conn = databaseAdapter::getInstance();
	}
	
	public function add(){
	if($_POST['action']=='addServicio.do')
		{
			$this->_conn->connect();
			$_query = sprintf("INSERT INTO servicios(vehiculos_id, fecha_ingreso, tiempo, und_tiempo) VALUES ('%s', '%s', %d, '%s') returning id",$_POST['placa'],date("Y-m-d"),$_POST['tiempo'],$_POST['und_tiempo']);
			$result = $this->_conn->execute($_query);
			$insert_row = pg_fetch_row($_result);
			$_query2 = sprintf("INSERT INTO users_servicios(users_id, servicios_id) VALUES(%d, '%s')",1,$insert_row[0]);
			$this->_conn->execute($_query2);
			$this->_conn->close();
			unset($_POST['action']);
			//echo '{ "respuesta" : "200"}';
		}
	}
}