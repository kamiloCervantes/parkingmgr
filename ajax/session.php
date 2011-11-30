<?php
include_once '../controllers/session_controller.php';

if(isset($_POST['action']))
{
	$_session = new Session_Controller();
	switch($_POST['action'])
	{
		case 'login.do' : $_session->login(); break;
		case 'logout.do' : $_session->logout(); break;
	}
}
