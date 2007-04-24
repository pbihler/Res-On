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
         $this->setTitle("View Your Results Online"); 
         $this->menu = array("Admin"=>"admin.php") + $this->menu; 
     }
     
     function renderNotes() {
         
         //Results requested?
         if (isset($_POST['mat_no']) && $_POST['mat_no']) {
             $this->renderResult();
         } else {
	     	 $this->renderNote($this->getResultRequestForm(),'Request results');
	     	 $this->writeJavascript('document.request_results_form.mat_no.focus();');
         }
     }
     
     private function renderResult() {
         $mat_no = $_POST['mat_no'];
         if (! ctype_alnum($mat_no)) {
             $this->renderError('Matriculation number contains non-alphanumerical characters');
         	 return;
         }
         $project_id = $_POST['project_id'];
         if (! ctype_digit($project_id)) {
             $this->renderError('Project-id invalid');
         	 return;
         }
         $pwd = $_POST['password'];
         if (! $pwd) {
             $this->renderError('Password empty');
             return;
         }
         $result_str = 'No results for this combination of matriculation number and password found.';
         if (preg_match(PasswordGenerator::$passwordCharacterRegExp,$pwd)) {
             //If not, we dont query the database, but we won't tell the intruder either
	        $db = Database::getInstance();
         	$data = $db->getResultDataByMatNo($project_id,$mat_no);
            $crypt = new CryptProxy($data['crypt_module'], $project_id,$data['member_id']);
         	$decrypted_result = $crypt->decryptResult($data['result'],$data['crypt_data'],$pwd);
         	if ($decrypted_result)
         		$result_str = sprintf('<div class="result">%s</div>',$decrypted_result);
         }
         $this->renderNote($result_str,sprintf('Results for matriculation number %s:',$mat_no));
         
     }
     
     private function renderError($error) {
             $this->renderNote(sprintf('<div class="error">%s</div>' .
             		'<div class="back"><input type="button" value="Back" onclick="history.back();" /></div>',$error),'Error');         
     }
     private function getResultRequestForm() {
     	return sprintf('<div id="requestresults"><form method="POST" name="request_results_form" autocomplete="off">' .
     			'<input type="hidden" name="project_id" size="3" value="%03d" readonly="readonly" />' .
     			'Mat.-Number: <input type="text" name="mat_no" value="" size="10" /><br />' .
     			'Password: <input type="password" name="password" value="" size="10" /><br />' .
     			'<input type="submit" value="Request Results" />' .
     			'</form></div>' .
     			'',MainConfig::$default_project_id);
     }
     
 }
?>
