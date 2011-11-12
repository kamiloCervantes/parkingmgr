<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$rootPath = dirname(dirname(__FILE__));
set_include_path(get_include_path() . PATH_SEPARATOR . $rootPath . '/controllers' . PATH_SEPARATOR
									. PATH_SEPARATOR . '/models' . PATH_SEPARATOR . '/views' . PATH_SEPARATOR);

include_once 'conf/dbconf.php';									
class databaseAdapter {

   //Datos de conexion
	private $_user;
	private $_password;
	private $_database;
	private $_server;
    private $_databaseManager;

	//Variable de conexion y consulta
	private $_conn;

	//Variable estatica para implementar singleton
	private static $INSTANCIA_DE_CLASE;
	
	//Constructor
	private function __construct(){
		$this->setConnectDefault();
	}

	//Metodo para crear una instancia unica de la clase.
	public static function getInstance(){
		if(!self::$INSTANCIA_DE_CLASE instanceof self)
		{
			self::$INSTANCIA_DE_CLASE = new self();
		}
		return self::$INSTANCIA_DE_CLASE;
	}

	//Conectarse con una base de datos en Postgresql
	private function connectPostgres(){
		$this-> _conn=pg_connect("host=".$this->_server.
					 " dbname=".$this->_database.
					 " user=".$this->_user.
					 " password=".$this->_password."")
				         or die("<br>Error: Conexion fallida.<br>");
	}
        
        private function  closePostgres(){
        	pg_close($this->_conn);
        }

	//Ejecutar consulta en postgresql
	private function executePostgres($query){
		//echo $query;
		$respuesta = pg_query($this->_conn, $query) or die(pg_errormessage($this->_conn));
                return $respuesta;
	}

    private function countRowPostgres($result){
    	return pg_num_rows($result);
    }

	public function __clone()
   	{
            trigger_error("Operacion Invalida: No puedes clonar una instancia de ". get_class($this) ." class.", E_USER_ERROR );
   	}

   	public function __wakeup()
   	{
            trigger_error("No puedes deserializar una instancia de ". get_class($this) ." class.");
   	} 
}