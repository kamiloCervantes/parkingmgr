<?php

include_once '../models/databaseAdapter.php';

class Servicios_Controller
{
	private $_conn;
	
	public function __construct()
	{
		$this->_conn = databaseAdapter::getInstance();
	}
	
	public function add()
	{
	if($_POST['action']=='addServicio.do')
		{
			$this->_conn->connect();
			$_query = sprintf("INSERT INTO servicios(vehiculos_id, fecha_ingreso, tiempo, und_tiempo, precio) VALUES ('%s', '%s', %d, '%s',%d) returning id",strtoupper($_POST['placa']),date("Y-m-d"),$_POST['tiempo'],$_POST['und_tiempo'],$_POST['precio']);
			$result = $this->_conn->execute($_query);
			$insert_row = pg_fetch_row($result);
			$insert_id = $insert_row[0];
			$_query2 = sprintf("INSERT INTO users_servicios(users_id, servicios_id) VALUES(%d, '%s')",1,$insert_id);
			$this->_conn->execute($_query2);
			$this->_conn->close();
			unset($_POST['action']);
		}
	}
	
	public function get()
	{
		if($_POST['action']=='getServicios.do')
		{
			$this->_conn->connect();
			//select distinct servicios.vehiculos_id, sum(valor_pago) from servicios,users_servicios,pagos,users_pagos where 
			//servicios.id=users_servicios.id and pagos.id=users_pagos.id  and servicios.id=pagos.servicios_id and 
			//servicios.vehiculos_id='%s'group by servicios.vehiculos_id;
			$_query = sprintf("select distinct servicios.vehiculos_id, sum(valor_pago) as pagos from servicios,users_servicios,pagos,users_pagos where
								servicios.id=users_servicios.id and pagos.id=users_pagos.id  and servicios.id=pagos.servicios_id and 
								servicios.vehiculos_id='%s'group by servicios.vehiculos_id",strtoupper($_POST['placa']));
			$result = $this->_conn->execute($_query);
			$servicios_array = $this->_conn->fetch_assoc($result);
			/*
			 * 		select 
					distinct servicios.vehiculos_id, sum(servicios.precio) as deuda
					from servicios,users_servicios 
					where servicios.id=users_servicios.id 
					and servicios.vehiculos_id = '%s'
					group by servicios.vehiculos_id
			 */
			$_query2 = sprintf("select distinct servicios.vehiculos_id, sum(servicios.precio) as deuda from servicios,users_servicios
			where servicios.id=users_servicios.id and servicios.vehiculos_id = '%s' group by servicios.vehiculos_id",strtoupper($_POST['placa']));
			$result2 = $this->_conn->execute($_query2);
			$deudas_array = $this->_conn->fetch_assoc($result2);
			$servicios_array['deuda'] = $deudas_array['deuda'];
			/*
			 * select distinct servicios.id, servicios.fecha_ingreso, servicios.tiempo, servicios.und_tiempo 
				from servicios,users_servicios
				where servicios.id=users_servicios.id 
				and servicios.vehiculos_id = '%s'
				order by servicios.id
			 */
			$_query3 = sprintf("select distinct servicios.id, servicios.fecha_ingreso, servicios.tiempo, servicios.und_tiempo from servicios,users_servicios
			where servicios.id=users_servicios.id and servicios.vehiculos_id = '%s' order by servicios.id",strtoupper($_POST['placa']));
			$result3 = $this->_conn->execute($_query3);
			while($servicios_detail_array = $this->_conn->fetch_assoc($result3))
			{
				$servicios_array['services'][] = $servicios_detail_array;
			}
			echo json_encode($servicios_array);
			$this->_conn->close();
		}
	}
}