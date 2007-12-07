<?php
/*
 * Created on 20.03.2007
 *
 * With this page it is possible to add new projects (exams) to the site
 * 
 * Licenced under GPL: http://www.gnu.org/licenses/gpl.txt
 *
 */
 
 //Check for PHP 5:
 if (version_compare("5.0.0",phpversion()) > 0) {
	 echo "<html><head><title>PHP 5 required</title></head><body><h1>Error</h1><p>This program requires PHP 5.0.0 or later.</p></body></html>";
	 exit;
 }
 
 require_once('classes/autoload.php');
 
 $page = new SetupPage();
 $page->render();
 
?>
