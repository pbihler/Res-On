<?php
/*
 * Created on 17.04.2007 by bihler
 *
 * Displays a PDF-File containing R-Keys and Password for printout purposes
 * 
 * Licenced under GPL: http://www.gnu.org/licenses/gpl.txt
 *
 */
 
 require_once('classes/autoload.php');
 
$page = AdminPageFactory::factory('KeyPDFPage');
 	
$page->render();

?>
