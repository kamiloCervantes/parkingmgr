<?php
include_once '../controllers/pagos_controller.php';

if(isset($_POST['action']))
{
$_pagos = new Pagos_Controller();
	/* actions */
		switch($_POST['action'])
		{
			case 'addPago.do': $_pagos->add(); break;
		}
}