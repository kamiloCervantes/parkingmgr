<?php

include_once '../models/databaseAdapter.php';

class Pagos_Controller
{
	private $_conn;
	
	public function __construct()
	{
		$this->_conn = databaseAdapter::getInstance();
	}
	
	public function add()
	{
		if($_POST['action']=='addPago.do')
		{
			$this->_conn->connect();
			$_query = sprintf("INSERT INTO pagos(valor_pago, fecha_pago, servicios_id) VALUES (%d, '%s', %d) returning id",$_POST['valor_pago'],$_POST['fecha_pago'],$_POST['servicio_id']);
			$result = $this->_conn->execute($_query);
			$insert_row = pg_fetch_row($result);
			$insert_id = $insert_row[0];
			$_query2 = sprintf("INSERT INTO users_pagos(users_id, pagos_id) VALUES (%d, %d)",1,$insert_id);
			$this->_conn->execute($_query2);
			$this->_conn->close();
			unset($_POST['action']);
		}
	}
}