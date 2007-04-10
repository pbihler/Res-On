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
     	 $this->writeJavascript('document.request_results_form.member_id.focus();');
     }
     
     private function getResultRequestForm() {
     	return sprintf('<div id="requestresults"><form method="POST" name="request_results_form">' .
     			'R-Key: <input type="text" name="project_id" size="3" value="%03d" readonly="readonly" />-<input type="text" name="member_id" value="" size="7" maxlength="7" /><br />' .
     			'Matric.-Number: <input type="text" name="mat_no" value="" /><br />' .
     			'Password: <input type="password" name="password" value="" size="20" /><br />' .
     			'<input type="submit" value="Request Results" />' .
     			'</form></div>' .
     			'',MainConfig::$default_project_id);
     }
     
 }
?>
