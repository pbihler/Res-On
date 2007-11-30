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
         return '<form action="admin.php" name="login_form" method="post"><div id="loginform">' .
         		sprintf('  %s: ',Messages::getString('LoginPage.EnterPassword')) .
         		'  <input type="password" name="pwd" value="" /> ' .
         		'  <input type="submit" value="Login" />' .
         		'</div></form>&nbsp;';
     }
     
 }
?>
