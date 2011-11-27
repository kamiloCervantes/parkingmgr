<?php

include_once '../models/databaseAdapter.php';
include_once 'pagos_controller.php';
include("../libs/pChart/class/pData.class.php"); 
include("../libs/pChart/class/pDraw.class.php"); 
include("../libs/pChart/class/pImage.class.php"); 

class Graficos_Controller
{
	private $_conn;
	private $_pagos;
	
	public function __construct()
	{
		$this->_conn = databaseAdapter::getInstance();
		$this->_pagos = new Pagos_Controller();
	}
	
	public function plotIngresosSemanales()
	{
		$ingresos_data = $this->_pagos->getDataIngresos();
		$fechas_data = $this->_pagos->getDataFechas();
		/* Create and populate the pData object */ 
		/*$plot_data = $this->_pagos->getPlotData();
		$i = 0;
		foreach($plot_data as $plot)
		{
			$ingresos_data[$i] = $plot["sum"]; 
		}
		echo json_encode($ingresos_data);*/
		//echo json_encode($plot_data);
 		$MyData = new pData();   
 		//$MyData->addPoints(array(3,12,15,8,5,7),"Probe 1"); 
 		$MyData->addPoints($ingresos_data,"Probe 1"); 
	 	$MyData->setSerieWeight("Probe 1",2); 
	 	$MyData->setAxisName(0,"Ingresos"); 
	 	//$MyData->addPoints(array("Jan","Feb","Mar","Apr","May","Jun"),"Labels"); 
	 	$MyData->addPoints($fechas_data,"Labels");
	 	$MyData->setSerieDescription("Labels","Months"); 
	 	$MyData->setAbscissa("Labels"); 
	 	
	 	/* Create the pChart object */ 
 		$myPicture = new pImage(700,230,$MyData); 
 		
 		/* Turn of Antialiasing */ 
 		$myPicture->Antialias = FALSE; 

		/* Draw the background */ 
	 	$Settings = array("R"=>170, "G"=>183, "B"=>87, "Dash"=>1, "DashR"=>190, "DashG"=>203, "DashB"=>107); 
	 	$myPicture->drawFilledRectangle(0,0,700,230,$Settings); 
	
	 	/* Overlay with a gradient */ 
	 	$Settings = array("StartR"=>219, "StartG"=>231, "StartB"=>139, "EndR"=>1, "EndG"=>138, "EndB"=>68, "Alpha"=>50); 
	 	$myPicture->drawGradientArea(0,0,700,230,DIRECTION_VERTICAL,$Settings); 
	 	$myPicture->drawGradientArea(0,0,700,20,DIRECTION_VERTICAL,array("StartR"=>0,"StartG"=>0,"StartB"=>0,"EndR"=>50,"EndG"=>50,"EndB"=>50,"Alpha"=>80)); 
	
	 	/* Add a border to the picture */ 
	 	$myPicture->drawRectangle(0,0,699,229,array("R"=>0,"G"=>0,"B"=>0)); 
	  
	 	/* Write the chart title */  
	 	$myPicture->setFontProperties(array("FontName"=>"../libs/pChart/fonts/Forgotte.ttf","FontSize"=>8,"R"=>255,"G"=>255,"B"=>255)); 
	 	$myPicture->drawText(10,16,"Ingresos de la semana",array("FontSize"=>11,"Align"=>TEXT_ALIGN_BOTTOMLEFT)); 
	
	 	/* Set the default font */ 
	 	$myPicture->setFontProperties(array("FontName"=>"../libs/pChart/fonts/pf_arma_five.ttf","FontSize"=>6,"R"=>0,"G"=>0,"B"=>0)); 
	
	 	/* Define the chart area */ 
	 	$myPicture->setGraphArea(60,40,650,200); 
	
	 	/* Draw the scale */ 
	 	$scaleSettings = array("XMargin"=>10,"YMargin"=>10,"Floating"=>TRUE,"GridR"=>200,"GridG"=>200,"GridB"=>200,"DrawSubTicks"=>TRUE,"CycleBackground"=>TRUE); 
	 	$myPicture->drawScale($scaleSettings); 
	
	 	/* Turn on Antialiasing */ 
	 	$myPicture->Antialias = TRUE; 
	
	 	/* Enable shadow computing */ 
	 	$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10)); 
	
	 	/* Draw the line chart */ 
	 	$myPicture->drawLineChart(); 
	 	$myPicture->drawPlotChart(array("DisplayValues"=>TRUE,"PlotBorder"=>TRUE,"BorderSize"=>2,"Surrounding"=>-60,"BorderAlpha"=>80)); 
	
	 	/* Write the chart legend */ 
	 	$myPicture->drawLegend(590,9,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL,"FontR"=>255,"FontG"=>255,"FontB"=>255)); 
	
	 	/* Render the picture (choose the best way) */ 
	 	$myPicture->autoOutput("../libs/pChart/pictures/example.drawLineChart.plots.png");
	}
	
	
}