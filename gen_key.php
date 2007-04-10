<?php
/*
 * Created on 30.03.2007 by pascal
 *
 * Generate new R-Keys
 * 
 * Licenced under GPL: http://www.gnu.org/licenses/gpl.txt
 * 
 */
 require_once('classes/autoload.php');
 
$page = AdminPageFactory::factory('GenKeyPage');
 	
$page->render();
?>
