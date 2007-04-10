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
         $this->setTitle("Administration-Login");
         $this->menu = array("Home"=>"index.php") + $this->menu; 
         $this->message = $message;
     }
     
     function renderNotes() {
         
         $note = $this->getLoginForm();
         if ($this->message) 
             $note = $this->formatError($this->message) . $note;
         
         $this->renderNote($note,'Please login to access the administration:');
     	 $this->writeJavascript('document.login_form.pwd.focus();');
     }
     
     private function getLoginForm() {
         return '<form action="admin.php" name="login_form" method="post"><div id="loginform">' .
         		'  Please enter your password: ' .
         		'  <input type="password" name="pwd" value="" /> ' .
         		'  <input type="submit" value="Login" />' .
         		'</div></form>';
     }
     
 }
?>
