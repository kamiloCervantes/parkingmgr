<?php

include_once '../controllers/servicios_controller.php';

if(isset($_POST['action']))
{
	$_servicios = new Servicios_Controller();
	/* actions */
		switch($_POST['action'])
		{
			case 'addServicio.do': $_servicios->add(); break;
		}
	//$_servicios->add();
}
