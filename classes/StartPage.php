<?php 
/*
 * Created on 21.03.2007
 *
 * Defines the content and actions of the Startpage
 * 
 * Licenced under GPL: http://www.gnu.org/licenses/gpl.txt
 *
 */
 class StartPage extends Page {
     function __construct() {
         parent::__construct();
         $this->setTitle("View your Results online"); 
         $this->menu = array("Admin"=>"admin.php") + $this->menu; 
     }
     
     function renderNotes() {
     	 $this->renderNote($this->getResultRequestForm(),'Request results');
     	 $this->writeJavascript('document.request_results_form.mat_no.focus();');
     }
     
     private function getResultRequestForm() {
     	return sprintf('<div id="requestresults"><form method="POST" name="request_results_form" autocomplete="off">' .
     			'<input type="hidden" name="project_id" size="3" value="%03d" readonly="readonly" />' .
     			'Mat.-Number: <input type="text" name="mat_no" value="" /><br />' .
     			'Password: <input type="password" name="password" value="" size="20" /><br />' .
     			'<input type="submit" value="Request Results" />' .
     			'</form></div>' .
     			'',MainConfig::$default_project_id);
     }
     
 }
?>
