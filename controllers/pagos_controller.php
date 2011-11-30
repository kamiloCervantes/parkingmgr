<?php

include_once '../models/databaseAdapter.php';

class Pagos_Controller
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
			if($_POST['action']=='addPago.do')
			{
				$this->_conn->connect();
				$_query = sprintf("INSERT INTO pagos(valor_pago, fecha_pago, servicios_id) VALUES (%d, '%s', %d) returning id",$_POST['valor_pago'],$_POST['fecha_pago'],$_POST['servicio_id']);
				$result = $this->_conn->execute($_query);
				$insert_row = pg_fetch_row($result);
				$insert_id = $insert_row[0];
				$_query2 = sprintf("INSERT INTO users_pagos(users_id, pagos_id) VALUES (%d, %d)",$_SESSION['id'],$insert_id);
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
	
	public function getDataReportes()
	{	
		if(isset($_SESSION['id']))
		{
			if($_POST['action']=='getDataReportes.do')
			{
				$this->_conn->connect();
	
				$_query = sprintf("select sum(pago),mes from (SELECT sum(valor_pago) as pago, fecha_pago, extract(month from fecha_pago)
				as mes FROM pagos,users_pagos where pagos.id=users_pagos.pagos_id and users_pagos.users_id=%d group by fecha_pago
				order by fecha_pago) as sub where mes=%d group by mes",$_SESSION['id'],$_POST['month']);
				$result = $this->_conn->execute($_query);
				if($this->_conn->rowCount($result)>0)
				{
					while($tmp = $this->_conn->fetch_assoc($result))
					{
						$plot_data["mes"][] = $tmp;
					}	
				}
				else
				{
					$plot_data["mes"] = "0";
				}
				
				
				$_query2 = sprintf("SELECT sum(valor_pago), fecha_pago, extract(dow from fecha_pago) as dow, extract(week from fecha_pago) as week
							  FROM pagos,users_pagos where pagos.id=users_pagos.pagos_id and users_pagos.users_id=%d group by fecha_pago
							  having extract(week from fecha_pago)=%d order by fecha_pago",$_SESSION['id'],date('W',mktime(0,0,0,$_POST['month'],$_POST['day'],$_POST['year'])));
				$result2 = $this->_conn->execute($_query2);
				if($this->_conn->rowCount($result2)>0)
				{
					while($tmp = $this->_conn->fetch_assoc($result2))
					{
						$plot_data["semana"][] = $tmp;
					}
				}
				else
				{
					$plot_data["semana"] = "0";
				}
	
				$_query3 = sprintf("SELECT vehiculos_id, valor_pago, fecha_ingreso, fecha_pago FROM pagos,users_pagos,servicios,users_servicios 
									where users_pagos.pagos_id=pagos.id and pagos.servicios_id=servicios.id and servicios.id=users_servicios.servicios_id
									and users_servicios.servicios_id=pagos.servicios_id and users_pagos.users_id=%d and fecha_pago='%s'",$_SESSION['id'],$_POST['fecha']);
				$result3 = $this->_conn->execute($_query3);
				if($this->_conn->rowCount($result3)>0)
				{
					while($tmp = $this->_conn->fetch_assoc($result3))
					{
						$plot_data["dia"][] = $tmp;
					}
				}
				else 
				{
					$plot_data["dia"] = "0";
				}
				$this->_conn->close();
				echo json_encode($plot_data);
			}
		}
		else 
		{
			echo '{ "login": "0" }';
		}
	}
}