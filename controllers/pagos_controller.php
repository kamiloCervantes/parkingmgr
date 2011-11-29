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
	
	public function getDataIngresos()
	{
		$this->_conn->connect();
		if(isset($_POST['week']))
		{
			$_query = sprintf("SELECT sum(valor_pago), fecha_pago, extract(dow from fecha_pago) as dow, extract(week from fecha_pago) as week
						  FROM pagos,users_pagos where pagos.id=users_pagos.pagos_id and users_pagos.users_id=%d group by fecha_pago
						  having extract(week from fecha_pago)=%d order by fecha_pago",1,$_POST['week']);
		}
		/*else 
		{
			$_query = sprintf("SELECT sum(valor_pago), fecha_pago, extract(dow from fecha_pago) as dow, extract(week from fecha_pago) as week
						  FROM pagos,users_pagos where pagos.id=users_pagos.pagos_id and users_pagos.users_id=%d group by fecha_pago
						  having extract(week from fecha_pago)=%d order by fecha_pago",1,date('W'));
		}*/
		
		$result = $this->_conn->execute($_query);
		/*for($i=0;$i<7;$i++)
		{
			$plot_data[$i] = 0;
		}*/
		while($tmp = $this->_conn->fetch_assoc($result))
		{
			$plot_data["pagos"][] = $tmp;
			//$plot_data[$tmp["dow"]] = $tmp["sum"];
		}
		//$plot_data["pagos"][0]["sum"] = "5000";
		$this->_conn->close();
		/*for($i=0;$i<7;$i++)
		{
			echo $plot_data[$i].'<br/>';
		}*/
		return $plot_data;
		//SELECT sum(valor_pago), fecha_pago, extract(dow from fecha_pago), extract(week from fecha_pago) as week 
		//FROM pagos,users_pagos where pagos.id=users_pagos.pagos_id and users_pagos.users_id=1 group by fecha_pago 
		//having extract(week from fecha_pago)=47 order by fecha_pago
	}
	
	public function getDataReportes()
	{
		if($_POST['action']=='getDataReportes.do')
		{
			$this->_conn->connect();
			/*
			 select sum(pago),mes from 
			(SELECT sum(valor_pago) as pago, fecha_pago, extract(month from fecha_pago) as mes
			FROM pagos,users_pagos where pagos.id=users_pagos.pagos_id and users_pagos.users_id=1 group by fecha_pago
			order by fecha_pago) as sub where mes=11 group by mes
			 */
			$_query = sprintf("select sum(pago),mes from (SELECT sum(valor_pago) as pago, fecha_pago, extract(month from fecha_pago)
			as mes FROM pagos,users_pagos where pagos.id=users_pagos.pagos_id and users_pagos.users_id=%d group by fecha_pago
			order by fecha_pago) as sub where mes=%d group by mes",1,$_POST['month']);
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
						  having extract(week from fecha_pago)=%d order by fecha_pago",1,date('W',mktime(0,0,0,$_POST['month'],$_POST['day'],$_POST['year'])));
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
			/*
			SELECT vehiculos_id, valor_pago, fecha_ingreso, fecha_pago FROM pagos,users_pagos,servicios,users_servicios 
			where users_pagos.pagos_id=pagos.id and pagos.servicios_id=servicios.id 
			and servicios.id=users_servicios.servicios_id and users_servicios.servicios_id=pagos.servicios_id 
			and users_pagos.users_id=1 and fecha_pago='2011-11-24'
			 */
			$_query3 = sprintf("SELECT vehiculos_id, valor_pago, fecha_ingreso, fecha_pago FROM pagos,users_pagos,servicios,users_servicios 
								where users_pagos.pagos_id=pagos.id and pagos.servicios_id=servicios.id and servicios.id=users_servicios.servicios_id
								and users_servicios.servicios_id=pagos.servicios_id and users_pagos.users_id=%d and fecha_pago='%s'",1,$_POST['fecha']);
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
	
	public function getDataFechas()
	{
		if(isset($_POST['week']))
		{
			for($i=0;$i<7;$i++)
			{
				$plot_data[$i] = $this->dateAdd($i,$_POST['week'],2011);
			}
		}
		else 
		{
			for($i=0;$i<7;$i++)
			{
				$plot_data[$i] = $this->dateAdd($i,date('W'),2011);
			}
		}
		/*for($i=0;$i<7;$i++)
		{
			echo $plot_data[$i].'<br/>';
		}*/
		return $plot_data;
		//SELECT sum(valor_pago), fecha_pago, extract(dow from fecha_pago), extract(week from fecha_pago) as week 
		//FROM pagos,users_pagos where pagos.id=users_pagos.pagos_id and users_pagos.users_id=1 group by fecha_pago 
		//having extract(week from fecha_pago)=47 order by fecha_pago
	}

	
	public function WeekStartDate($week,$year,$format="Y-m-d") { 
	    $firstDayInYear=date("N",mktime(0,0,0,1,1,$year)); 
	    if ($firstDayInYear<5) 
	         $shift=-($firstDayInYear-1)*86400; 
	    else 
	         $shift=(8-$firstDayInYear)*86400; 
	    if ($week>1) $weekInSeconds=($week-1)*604800; else $weekInSeconds=0; 
	    $timestamp=mktime(0,0,0,1,1,$year)+$weekInSeconds+$shift; 
	    return $timestamp;
	    //return date($format,$timestamp); 
 	} 

 	public function dateAdd($dias,$week,$year)
   {
      $mes = date("m",$this->WeekStartDate($week,$year));
      $anio = date("Y",$this->WeekStartDate($week,$year));
      $dia = date("d",$this->WeekStartDate($week,$year));
      $ultimo_dia = date( "d", mktime(0, 0, 0, $mes + 1, 0, $anio) ) ;
      $dias_adelanto = $dias;
      $siguiente = $dia + $dias_adelanto;
      if ($ultimo_dia < $siguiente)
      {
         $dia_final = $siguiente - $ultimo_dia;
         $mes++;
         if ($mes == '13')
         {
            $anio++;
            $mes = '01';
         }
         //$fecha_final = $dia_final.'/'.$mes.'/'.$anio;
         $fecha_final = $anio.'-'.$mes.'-'.$dia_final;         
      }
      else
      {
         //$fecha_final = $siguiente .'/'.$mes.'/'.$anio;  
         $fecha_final = $anio .'-'.$mes.'-'.$siguiente;        
      }
      return $fecha_final;
   }
}