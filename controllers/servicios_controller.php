<?php

include_once '../models/databaseAdapter.php';

class Servicios_Controller
{
	private $_conn;
	
	public function __construct()
	{
		$this->_conn = databaseAdapter::getInstance();
		session_start();
	}
	
	public function add()
	{
		if(isset($_SESSION['id']))
		{
			if($_POST['action']=='addServicio.do')
			{
				$this->_conn->connect();
				$_query = sprintf("INSERT INTO servicios(vehiculos_id, fecha_ingreso, tiempo, und_tiempo, precio) VALUES ('%s', '%s', %d, '%s',%d) returning id",strtoupper($_POST['placa']),date("Y-m-d"),$_POST['tiempo'],$_POST['und_tiempo'],$_POST['precio']);
				$result = $this->_conn->execute($_query);
				$insert_row = pg_fetch_row($result);
				$insert_id = $insert_row[0];
				$_query2 = sprintf("INSERT INTO users_servicios(users_id, servicios_id) VALUES(%d, '%s')",$_SESSION['id'],$insert_id);
				$this->_conn->execute($_query2);
				$this->_conn->close();
				unset($_POST['action']);
			}
		}
		else
		{
			echo '{ "login": "0" }';
		}
	}
	
	public function get()
	{
		if(isset($_SESSION['id']))
		{
			if($_POST['action']=='getServicios.do')
			{  
				$this->_conn->connect();
				$servicios_array = array();		
				$_query2 = sprintf("select distinct servicios.vehiculos_id, sum(servicios.precio) as deuda from servicios,users_servicios
				where servicios.id=users_servicios.id and servicios.vehiculos_id ='%s' and users_servicios.users_id=%d group by servicios.vehiculos_id",strtoupper($_POST['placa']),$_SESSION['id']);
				$result2 = $this->_conn->execute($_query2);
				$deudas_array = $this->_conn->fetch_assoc($result2);
				$servicios_array['deuda'] = $deudas_array['deuda'];	
				$servicios_array['vehiculos_id'] = $deudas_array['vehiculos_id'];			
				$_query3 = sprintf("select distinct servicios.id, servicios.fecha_ingreso, servicios.tiempo, servicios.und_tiempo, servicios.precio from servicios,users_servicios
				where servicios.id=users_servicios.id and servicios.vehiculos_id ='%s' and users_servicios.users_id=%d order by servicios.id",strtoupper($_POST['placa']),$_SESSION['id']);
				$result3 = $this->_conn->execute($_query3);
				while($servicios_detail_array = $this->_conn->fetch_assoc($result3))
				{	
					$_query4 = sprintf("select * from pagos,users_pagos where servicios_id=%d and users_pagos.pagos_id = pagos.id and users_id=%d", $servicios_detail_array['id'],$_SESSION['id']);
					$result4 = $this->_conn->execute($_query4);
					while($pagos_array =  $this->_conn->fetch_assoc($result4))
					{
						$servicios_array['pagos_service'][] = $pagos_array;
					}
					$_query5 = sprintf("SELECT servicios_id, sum(valor_pago) as subtotal FROM pagos,users_pagos where servicios_id=%d and users_pagos.pagos_id=pagos.id and users_pagos.users_id=%d group by servicios_id", $servicios_detail_array['id'],$_SESSION['id']);
					$result5 = $this->_conn->execute($_query5);
					$subtot_array =  $this->_conn->fetch_assoc($result5);
					if($subtot_array['subtotal']!=null)
					$servicios_detail_array['subtotal_pago'] = $subtot_array['subtotal'];
					else 
					$servicios_detail_array['subtotal_pago'] = "0";
					
					$servicios_array['services'][] = $servicios_detail_array;
				}
				echo json_encode($servicios_array);
				$this->_conn->close();
			}
		}
		else
		{
			echo '{ "login": "0" }';
		}
	}
	
	/*
	 * revisa si el servicio esta a paz y salvo o no
	 */
	public function checkServicio()
	{
		if(isset($_SESSION['id']))
		{
			if($_POST['action']=='checkServicio.do')
			{
				$this->_conn->connect();
				$_query = sprintf("SELECT pagos.servicios_id, sum(valor_pago) as subtotal 
				FROM pagos,users_pagos,servicios,users_servicios where users_pagos.pagos_id=pagos.id 
				and users_pagos.users_id=users_servicios.users_id and pagos.servicios_id=servicios.id 
				and pagos.servicios_id=users_servicios.servicios_id and servicios.id=pagos.servicios_id 
				and users_pagos.users_id=%d and pagos.servicios_id=%d and servicios.vehiculos_id='%s' 
				group by pagos.servicios_id",$_SESSION['id'],$_POST['servicio_id'],strtoupper($_POST['placa']));
				$result = $this->_conn->execute($_query);
				$pagos_check_info = $this->_conn->fetch_assoc($result);
				$_query2 = sprintf("select servicios.precio from servicios,users_servicios 
									where servicios.id=users_servicios.servicios_id and 
									users_servicios.users_id=%d and servicios.id=%d and 
									servicios.vehiculos_id='%s'",$_SESSION['id'],$_POST['servicio_id'],strtoupper($_POST['placa']));
				$result2 = $this->_conn->execute($_query2);
				$servicios_check_info = $this->_conn->fetch_assoc($result2);
				
				$check_info["precio"] = $servicios_check_info["precio"];
				
				
				//mirar si el servicio existe 
				if($servicios_check_info['precio']!=null)
				{
					if($pagos_check_info['subtotal']!=null){
						$check_info["subtotal"] = $pagos_check_info["subtotal"];
						if($servicios_check_info['precio'] > $pagos_check_info['subtotal'])
						{
							//no esta a paz y salvo
							$check_info["check"] = "0";
						}
						else 
						{
							if($servicios_check_info['precio'] == $pagos_check_info['subtotal'])
							{
								//si esta a paz y salvo
								$check_info["check"] = "1";
							}
							else 
							{
								//pago de mas
								$check_info["check"] = "-2";
							}
						}
					}
					else{
						//no esta a paz y salvo
						$check_info["check"] = "0";
					}
				}
				else 
				{
					//no existe el servicio
					$check_info["check"] = "-1";
					$check_info["subtotal"] = 0;
				}
				echo json_encode($check_info);
				$this->_conn->close();
			}
		}
		else
		{
			echo '{ "login": "0" }';
		}
	}
}