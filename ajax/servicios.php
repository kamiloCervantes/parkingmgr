<?php

include_once '../controllers/servicios_controller.php';

if(isset($_POST['action']))
{
	$_servicios = new Servicios_Controller();
	/* actions */
		switch($_POST['action'])
		{
			case 'addServicio.do': $_servicios->add(); break;
			case 'getServicios.do': $_servicios->get(); break;
			case 'checkServicio.do': $_servicios->checkServicio(); break;
		}
}
