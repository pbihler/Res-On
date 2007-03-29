<?php
/*
 * Created on 29.03.2007 by pascal
 *
 * Destroys the current session and show the Login-Form
 * 
 * Licenced under GPL: http://www.gnu.org/licenses/gpl.txt
 * 
 */
 
require_once('classes/autoload.php');
 
Session::destroySession();

// Show login form:
$page = new LoginPage;
$page->render();
?>
