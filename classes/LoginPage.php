<?php 
/*
 * Created on 21.03.2007
 *
 * To login for administration
 * 
 * Licenced under GPL: http://www.gnu.org/licenses/gpl.txt
 *
 */
 class LoginPage extends Page {
     
 	private $message = null;
 	
     function __construct($message = null) {
         parent::__construct();
         $this->setTitle(Messages::getString('LoginPage.Title'));
         $this->menu = array(Messages::getString('General.Home')=>"index.php") + $this->menu; 
         $this->message = $message;
     }
     
     function renderNotes() {
         
         $note = $this->getLoginForm();
         if ($this->message) 
             $note = $this->formatError($this->message) . $note;
         
         $this->renderNote($note,Messages::getString('LoginPage.PleaseLogin'));
     	 $this->writeJavascript('document.login_form.pwd.focus();');
     }
     
     private function getLoginForm() {
     	
     	  try {
	         $db = Database::getInstance();         
	         $frontpage_info = $db->getFrontpageInfo();
	     } catch (Exception $exception){ // in this case, render exception as error.
			 return $exception;
		 }
		 
		 // set project to select
		 $default_project_id = Config::$default_project_id;
         if (isset($_POST['project_id'])) {
             $default_project_id = intval($_POST['project_id']);
         }
		 
         $result = '<form action="admin.php" name="login_form" method="post"><div id="loginform">';
         if (count($frontpage_info) > 1) {
	        $result .= sprintf('<label for="project_id">%s: </label>',Messages::getString('General.Project'));
	     	$result .= '<select name="project_id" id="project_id_selector">';
	     	foreach ($frontpage_info as $id => $project) {
	     	    $result .= sprintf('<option value="%03d" %s>%s&nbsp;&nbsp;</option>',$id,
	     	        ($id == $default_project_id ? 'selected="selected"' : ''),
	     	        		$project->name);
	     	}	
	     	$result .= '</select><br/>';
     	 } else {
     		foreach ($frontpage_info as $id => $project) {
	     	    $result .= sprintf('<input type="hidden" name="project_id" value="%03d" />',$id);
	     	}	
     	 }
         $result .=  		sprintf('<label for="password">%s: </label>',Messages::getString('LoginPage.EnterPassword')) .
         		'  <input type="password" name="pwd" value="" /> ' .
         		'  <input type="submit" value="Login" />' .
         		'</div></form>&nbsp;';
         return $result;
     }
     
 }
?>
