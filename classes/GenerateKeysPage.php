<?php
/*
 * Created on 30.03.2007 by pascal
 *
 * The framework to generate new Reson-Keys
 * 
 * Since the genaration of a key might take longer, the procedure is
 * two stepped, with javascript as glue-code. This file is the framework
 * calling gen_key.php?... for each key to generate.
 * This page reports back the new generated key and password to
 * the framwork via javascript-callback. With this technique, the
 * total generation of keys can take longer than the PHP-timeout.
 * 
 * Licenced under GPL: http://www.gnu.org/licenses/gpl.txt
 * 
 */
 class GenerateKeysPage extends AdminPage {
 	
     private $GEN_KEY_PHP = 'gen_key.php';
     private $KEY_PDF_PHP = 'key_pdf.php';
     
 	 function __construct() {
         parent::__construct();
         $this->setTitle(sprintf("Generate new R-Keys for %s (Id: %03d)", $this->project->getName(),$this->project->getId()));
         $this->menu = array("Admin Menu"=>"admin.php") + $this->menu; 
     }
     
     protected function renderNotes() {
         
         $this->writeJavascript('var current_key = 0; var max_key = 0; var crypt_module = "";' . 
                                'function clickRunButton() {' . 
                                '    if (current_key == 0) {' . 
                                '      max_key = parseInt(document.keygen_form.key_count.value,0);' . 
                                '      if (isNaN(max_key) || max_key == 0) { '.
                                '          alert("Please enter a number > 0");' .
                                '          document.keygen_form.key_count.focus();' .
                                '          return;' .
                                '      }' .
                                '      crypt_module = document.keygen_form.crypt_module[document.keygen_form.crypt_module.selectedIndex].value;' .
                                '      document.keygen_form.run_button.value="Cancel";' .
                                '      document.getElementById("key_output").innerHTML = "<form action=\"' . $this->KEY_PDF_PHP . '\" method=\"POST\" target=\"_blank\"><table id=\"key_output_table\"><tbody id=\"key_output_table_body\"><tr><th class=\"out\">R-Key</th><th class=\"out\">Password</th></tr></tbody></table><input type=\"submit\" value=\"Generate PDF Handout\" /><input type=\"hidden\" name=\"ext\" value=\".pdf\" /></form>";' .
                                '      generate();' . 
                                '    } else { '. 
                                '      stop();' . 
                                '    }' . 
                                '}' . 
                                'function generate() {' . 
                                '    current_key++;' .
                                '    if (current_key > max_key) { stop(); return; }' . 
                                '    document.keygen_form.run_button.value="Cancel";' .
                                '    keygen_frame.location.href = "' . $this->GEN_KEY_PHP . '?crypt=" + escape(crypt_module) + "&current=" + current_key + "&max=" + max_key;' .
                                '}' .
                                'function store_result(reson_key,password) {' .
                                '   add_table_line(reson_key,password);' .
                                '       window.setTimeout("generate()", 10);' . // call next key generation, but process messages in between
                                '}' . 
                                'function stop() {' . 
                                '    current_key = 0;' .
                                '    max_key = 0;' . 
                                '    document.keygen_form.run_button.value="Run";'. 
                                '}' .
                                'function add_table_line(key,pwd) {' .
                                '    table = document.getElementById("key_output_table_body");' .
                                '    newTR = document.createElement("tr");' .
                                '    table.appendChild(newTR);' .
                                '    newTR.innerHTML="<input type=\"hidden\" name=\"key[]\" value=\"" + key + "\" /><input type=\"hidden\" name=\"pwd[]\" value=\"" + pwd + "\" /><td class=\"out\">" + key + "</td><td class=\"out\">" + pwd + "</td>";' .
                                '}' );
                                
         $note = '<form name="keygen_form" onsubmit="clickRunButton(); return false;">' .
         		'Number of keys: <input type="text" name="key_count" value="1" size="5" />' .
         		'&nbsp;<input type="button" name="run_button" value="Run" onclick="clickRunButton();" />' .
         		'<br />' .
         		'Encryption module: ' . $this->getCryptSelect() .
         		'</form>' .
         		'<iframe src="'. $this->GEN_KEY_PHP . '" name="keygen_frame" id="keygen_frame" scrolling="no">No Key-Generation</iframe><br />';         
         $this->renderNote($note,'R-Key Generation');      
         $this->renderNote('<div id="key_output" style="text-align:center">None</div>','New R-Keys');

     }
     
     private function getCryptSelect() {
     	$result = '<select name="crypt_module">';
     	foreach (array_keys(MainConfig::$crypt_info) as $module) {
     		$result .= '<option value="' . $module .'"';
     		if ($module == MainConfig::$default_crypt_module)
     		   $result .= ' selected="selected"';
     		$result .= '>' . $module . '</option';
     	}
     	$result .= '</select>';
     	return $result;
     }
 }
?>
