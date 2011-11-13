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
		
	}
}