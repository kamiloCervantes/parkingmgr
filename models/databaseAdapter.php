<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

include_once 'conf/dbconf.php';		
							
class databaseAdapter 
{

   //Datos de conexion
	private $_user;
	private $_password;
	private $_database;
	private $_server;

	//Variable de conexion y consulta
	private $_conn;

	//Variable estatica para implementar singleton
	private static $INSTANCIA_DE_CLASE;
	
	//Constructor
	private function __construct()
	{
		$this->setConnectDefault();
	}

	//Metodo para crear una instancia unica de la clase.
	public static function getInstance()
	{
		if(!self::$INSTANCIA_DE_CLASE instanceof self)
		{
			self::$INSTANCIA_DE_CLASE = new self();
		}
		return self::$INSTANCIA_DE_CLASE;
	}

	//Conectarse con una base de datos en Postgresql
	public function connect()
	{
		$this-> _conn=pg_connect(
								"host=".$this->_server.
								" dbname=".$this->_database.
								" user=".$this->_user.
					 			" password=".$this->_password."")
				      or die("<h1>Error: Conexion fallida.<h1>");
	}
        
    public function close()
    {
       	pg_close($this->_conn);
    }

	//Ejecutar consulta en postgresql
	public function execute($query)
	{
		$respuesta = pg_query($this->_conn, $query) or die(pg_errormessage($this->_conn));
        return $respuesta;
	}
	
	public function fetch_json($result)
	{
		return json_encode(pg_fetch_assoc($result));
	}
	
	public function fetch_assoc($result)
	{
		return pg_fetch_assoc($result);
	}
	
	public function fetch_object($result)
	{
		return pg_fetch_object($result);
	}
	
	public function rowCount($result)
	{
		return pg_num_rows($result);
	}
	
	 private function setConnectDefault()
	 {
        	$info = new dbconf();
      		$this->_database = $info->getDatabase();
      		$this->_user = $info->getUser();
      		$this->_password = $info->getPassword();
      		$this->_server = $info->getServer();
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