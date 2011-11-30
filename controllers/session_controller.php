<?php

include_once '../models/databaseAdapter.php';

class Session_Controller
{
	private $_conn;
	
	public function __construct()
	{
		$this->_conn = databaseAdapter::getInstance();
		session_start();
	}
	
	public function login()
	{
		if($_POST['action']=='login.do')
		{
			$this->_conn->connect();
			$_query = sprintf("select id, username, password from users where username='%s' and password='%s'",$_POST['user'],$_POST['pass']);
			$result = $this->_conn->execute($_query);
			if($this->_conn->rowCount($result)>0)
			{
				$tmp = $this->_conn->fetch_assoc($result);
				$_SESSION['id'] = $tmp['id'];
				echo '{ "login": "1" }';
				//header('location: ../nuevoingreso.html');
			}
			else
			{
				echo '{ "login": "0" }';
				//header('location: ../views/index.html');
			}
		}
	} 
	
	public function logout(){
		if($_POST['action']=='logout.do'){ 
			session_unset();
			session_destroy();
			echo '{ "logout": "1" }';
		}
		else
		{
			echo '{ "logout": "0" }';
		}
	}
}
	