<?php

include_once '../controllers/vehiculos_controller.php';

if(isset($_POST['action']))
{
	$_vehiculos = new Vehiculos_Controller();
	/* actions */
		switch($_POST['action'])
		{
			case 'addVehiculo.do': $_vehiculos->add(); break;
			case 'getVehiculo.do': $_vehiculos->get(); break;
		}
	//$_vehiculos->add();	
}
