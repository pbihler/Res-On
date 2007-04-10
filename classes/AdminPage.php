<?php
/*
 * Created on 30.03.2007 by pascal
 *
 * This is a abstract class for Administrational pages
 * 
 * Licenced under GPL: http://www.gnu.org/licenses/gpl.txt
 * 
 */
 abstract class AdminPage extends Page {
 	 protected $session = null;
 	 protected $project = null;
 	 
 	 function __construct() {
         parent::__construct();
         $this->session = Session::getInstance();
         $this->project = $this->session->getProject(); 
         $this->menu = array("Logout"=>"logout.php") + $this->menu; 
     }
 }
?>
