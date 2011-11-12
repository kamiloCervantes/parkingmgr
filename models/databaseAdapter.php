<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$rootPath = dirname(dirname(__FILE__));
set_include_path(get_include_path() . PATH_SEPARATOR . $rootPath . '/controllers' . PATH_SEPARATOR
									. PATH_SEPARATOR . '/models' . PATH_SEPARATOR . '/views' . PATH_SEPARATOR);
/**
 * Description of DbConnecting
 *Por favor, permíteme ver cuándo te conectas
 * @author luchox25
 */
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

	public function getDatabaseManager(){
		return $this->_databaseManager;
	}
	
	//Conectarse con una base de datos en Mysql
	private function connectMsql(){
		$this->_conn = mysql_connect($this->_server, $this->_user, $this->_password)
		or die(mysql_error());
		mysql_select_db($this->_database, $this->_conn);
                return $this->_conn;
	}

	//Conectarse con una base de datos en Postgresql
	private function connectPostgres(){
		$this-> _conn=pg_connect("host=".$this->_server.
					 " dbname=".$this->_database.
					 " user=".$this->_user.
					 " password=".$this->_password."")
				         or die("<br>Error: Conexion fallida.<br>");
	}
	
	//Metodo para establecer una conexion con el gestor de base de datos(Soporta postgres o mysql)
        public function connect(){
        	$okconnect = false;
            switch ($this->_databaseManager){
                case 'mysql':
                    $this->connectMsql();
                    $okconnect = true;
                    break;
                case 'postgresql':
                    $this->connectPostgres();
                    $okconnect = true;
                    break;
            }
			return $okconnect;
        }
        
        private function  closePostgres(){
        	pg_close($this->_conn);
        }
        
		private function  closeMysql(){
        	mysql_close($this->_conn);
        }
        
        public function close(){
         switch ($this->_databaseManager){
                case 'mysql':
                    return $this->closeMysql();
                    break;
                case 'postgresql':
                    return $this->closePostgres();
                    break;
            }
        }
        
	//Ejecutar consulta en mysql
    private function executeMsql($query)
	{
		$respuesta = mysql_query($query, $this->_conn) or die(mysql_error());
		return $respuesta;
	}

	//Ejecutar consulta en postgresql
	private function executePostgres($query){
		//echo $query;
		$respuesta = pg_query($this->_conn, $query) or die(pg_errormessage($this->_conn));
                return $respuesta;
	}

	//Ejecutar una consulta(Postgres o Mysql)
    public function execute($query, $invocador="ninguno"){
    	//echo "<h1>$query</h1>";
            switch ($this->_databaseManager){
                case 'mysql':
                    return $this->executeMsql($query);
                    break;
                case 'postgresql':
                    return $this->executePostgres($query);
                    break;
            }
        }

    private function countRowPostgres($result){
    	return pg_num_rows($result);
    }
    
    private function countRowMysql($result){
    	return mysql_num_rows($result);
    }
    
    public function countRows($result){
     		switch ($this->_databaseManager){
                case 'mysql':
                    return $this->countRowMysql($result);
                    break;
                case 'postgresql':
                    return $this->countRowPostgres($result);
                    break;
            }
    }
    
    private function retornarFilaPostgres($result){
    	$fil = pg_fetch_assoc($result);
    	return $fil;
    }

	private function retornarFilaMysql($result){
    	$fil = mysql_fetch_assoc($result);
    	return $fil;
    }
    
    public  function returnId(){
    	return mysql_insert_id($this->_conn);
    }
    
    public function retornarFila($result){
    		switch ($this->_databaseManager){
                case 'mysql':
                    return $this->retornarFilaMysql($result);
                    break;
                case 'postgresql':
                    return $this->retornarFilaPostgres($result);
                    break;
            }
    }
    
    
    	public function fetchObject($result){
    		switch ($this->_databaseManager){
    			case 'mysql':
                    return $this->fetchObjectMysql($result);
                    break;
                case 'postgresql':
                    return $this->fetchObjectPostgres($result);
                    break;
    		}
    	}
    
    	private function fetchObjectPostgres($result){
    		$result = pg_fetch_object($result);
    		return $result;
    	}
    	
		private function fetchObjectMysql($result){
    		$result = mysql_fetch_object($result);
    		return $result;
    	}
    	
    	public function free_Result($result){
    		switch ($this->_databaseManager){
    			case 'mysql':
                    return $this->free_ResultMysql($result);
                    break;
                case 'postgresql':
                    return $this->free_ResultPostgres($result);
                    break;
    		}
    	}
    	
		private function free_ResultMysql($result){
    		$res = mysql_free_result($result);
    		return $res;
    	}
    	
		private function free_ResultPostgres($result){
    		$res = pg_free_result($result);
    		return $res;
    	}
    	
        public function setConnectDefault(){
        	$info = new dbconf();
      		$this->_databaseManager = $info->getDatabaseManager();
      		$this->_database = $info->getDatabase();
      		$this->_user = $info->getUser();
      		$this->_password = $info->getPass();
      		$this->_server = $info->getHost();
        }
        
        public function setConnectDefaultXML(){
		$xml = simplexml_load_file("../../models/conf/db.xml");
                $cont = 0;
                foreach ($xml->database as $db){
                    $conf = utf8_decode($db['conf']);
                    if(strcasecmp($conf, "default")==0){
                        if($cont==0){
                            $this->_databaseManager=$db['databasemanager'];
                            $this->_server=$db->host;
                            $this->_database=$db->dbname;
                            $this->_user=$db->username;
                            $this->_password=$db->clave;
                        }
                        $cont++;
                    }
                }
                if($cont>1){
                        echo "<b class=msgError>Error: Se han configurado mas de una base de datos como default</b><br/>";
                        echo "<b class=msgAdvertencia>Abvertencia: Se tomara la primera configuracion hallada.</b><br/>";
                    }
                    else{
                        if($cont<1){
                            echo "<b class=msgError>Error de conexion: No se configuro ninguna base de datos como default</b><br/>";
                        }
                    }
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