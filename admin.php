<?php
/*
 * Created on 20.03.2007
 *
 * This is the entry to the adminsitration
 * 
 * Licenced under GPL: http://www.gnu.org/licenses/gpl.txt
 *
 */
 
 require_once('classes/autoload.php');
 
 $msg = null;
 
 //Get (or recreate) Session information
 if ($_GET['logout'] == 1) 
 	Session::destroySession();
 $session = Session::getInstance();
 
 //check, whether there was some login attempt:
 if ($password = $_POST['pwd']) {
 	$session = Session::createNewSession($password);
 	if (! $session->isLoggedIn())
 		$msg = "Password not correct";
 }
 
 if ($session->isLoggedIn())
 	$page = new AdminPage();
 else
 	$page = new LoginPage($msg);
 	
 $page->render();
 
?>
