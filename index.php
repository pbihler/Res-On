<?php
/*
 * Created on 20.03.2007
 *
 * This is the main entry page to the web-application
 * 
 * Licenced under GPL: http://www.gnu.org/licenses/gpl.txt
 *
 */
 
 require_once('classes/autoload.php');
 
 $page = new StartPage();
 $page->render();
 
?>
