<?php
include_once '../controllers/graficos_controller.php';

$_plots = new Graficos_Controller();
$_plots->plotIngresosSemanales();