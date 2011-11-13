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
			$_query = sprintf("INSERT INTO servicios(vehiculos_id, fecha_ingreso, tiempo, und_tiempo) VALUES ('%s', '%s', %d, '%s')",$_POST['placa'],date("Y-m-d"),$_POST['tiempo'],$_POST['und_tiempo']);
			$this->_conn->execute($_query);
			$this->_conn->close();
			echo '{ "respuesta" : "200"}';
		}
	}
}