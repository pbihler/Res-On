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
         $this->setTitle(sprintf(Messages::getString('GenerateKeysPage.Title'), $this->project->getName(),$this->project->getId()));
         $this->menu = array(Messages::getString('General.AdminMenu')=>"admin.php") + $this->menu; 
     }
     
     protected function renderNotes() {
         
         $this->writeJavascript('var current_key = 0; var max_key = 0; var crypt_module = "";' . 
                                'function clickRunButton() {' . 
                                '    if (current_key == 0) {' . 
                                '      max_key = parseInt(document.keygen_form.key_count.value,0);' . 
                                '      if (isNaN(max_key) || max_key == 0) { '.
                                sprintf('          alert("%s");',Messages::getString('GenerateKeysPage.EnterNumberBiggerZero')) .
                                '          document.keygen_form.key_count.focus();' .
                                '          return;' .
                                '      }' .
                                '      crypt_module = document.keygen_form.crypt_module[document.keygen_form.crypt_module.selectedIndex].value;' .
                                sprintf('      document.keygen_form.run_button.value="%s";',Messages::getString('General.Cancel')) .
                                sprintf('      document.getElementById("key_output").innerHTML = "<form action=\"' . $this->KEY_PDF_PHP . '\" method=\"POST\" target=\"_blank\"><table id=\"key_output_table\"><tbody id=\"key_output_table_body\"><tr><th class=\"out\">%s</th><th class=\"out\">%s</th></tr></tbody></table><input type=\"submit\" value=\"%s\" id=\"submit_data\" /><input type=\"hidden\" name=\"ext\" value=\".pdf\" /></form>&nbsp;";',Messages::getString('General.RKey'),Messages::getString('General.Password'),Messages::getString('GenerateKeysPage.GeneratePdf')) .
                                '      generate();' . 
                                '    } else { '. 
                                '      stop();' . 
                                '    }' . 
                                '}' . 
                                'function generate() {' . 
                                '    current_key++;' .
                                '    if (current_key > max_key) { stop(); return; }' . 
                                sprintf('    document.keygen_form.run_button.value="%s";',Messages::getString('General.Cancel')) .
                                '    keygen_frame.location.href = "' . $this->GEN_KEY_PHP . '?crypt=" + escape(crypt_module) + "&current=" + current_key + "&max=" + max_key;' .
                                '}' .
                                'function store_result(reson_key,password) {' .
                                '   add_table_line(reson_key,password);' .
                                '       window.setTimeout("generate()", 10);' . // call next key generation, but process messages in between
                                '}' . 
                                'function stop() {' . 
                                '    current_key = 0;' .
                                '    max_key = 0;' . 
                                sprintf('    document.keygen_form.run_button.value="%s";',Messages::getString('General.Run')). 
                                '}' .
                                'function add_table_line(key,pwd) {' .
                                '    table = document.getElementById("key_output_table_body");' .
                                '    newTR = document.createElement("tr");' .
                                '    table.appendChild(newTR);' .
                                '    newTD = document.createElement("td");' .
                                '    newTD.setAttribute("class","out");' .
                                '    newTR.appendChild(newTD);' .
                                '    text = document.createTextNode(key);' .
                                '    newTD.appendChild(text);' .
                                '    inp = document.createElement("input");' .
                                '    inp.setAttribute("type","hidden");' .
                                '    inp.setAttribute("name","key[]");' .
                                '    inp.setAttribute("value",key);' .
                                '    newTD.appendChild(inp);' .
                                '    newTD = document.createElement("td");' .
                                '    newTD.setAttribute("class","out");' .
                                '    newTR.appendChild(newTD);' .
                                '    text = document.createTextNode(pwd);' .
                                '    newTD.appendChild(text);' .
                                '    inp = document.createElement("input");' .
                                '    inp.setAttribute("type","hidden");' .
                                '    inp.setAttribute("name","pwd[]");' .
                                '    inp.setAttribute("value",pwd);' .
                                '    newTD.appendChild(inp);' .
                                '}' );
                                
         $note = '<form name="keygen_form" onsubmit="clickRunButton(); return false;">' .
         		sprintf('%s: <input type="text" name="key_count" value="1" size="5" />',Messages::getString('GenerateKeysPage.NumberOfKeys')) .
         		'<br />' .
         		sprintf('%s: %s',Messages::getString('GenerateKeysPage.EncryptionModule'),$this->getCryptSelect()) .
         		sprintf('&nbsp;<input type="button" name="run_button" id="submit_data" value="%s" onclick="clickRunButton();" />',Messages::getString('General.Run')) .
         		'</form>' .
         		sprintf('<iframe src="%s" name="keygen_frame" id="keygen_frame" scrolling="no" frameborder="0">%s</iframe><br />',$this->GEN_KEY_PHP,Messages::getString('GenerateKeysPage.NoKeyGeneration'));         
         $this->renderNote($note,Messages::getString('GenerateKeysPage.RKeyGeneration'));      
         $this->renderNote(sprintf('<div id="key_output" style="text-align:center">%s</div>',Messages::getString('General.None')),Messages::getString('GenerateKeysPage.NewRKeys'));

     }
     
     private function getCryptSelect() {
     	$result = '<select name="crypt_module">';
     	foreach (array_keys(Config::$crypt_info) as $module) {
     		$result .= '<option value="' . $module .'"';
     		if ($module == Config::$default_crypt_module)
     		   $result .= ' selected="selected"';
     		$result .= '>' . $module . '</option>';
     	}
     	$result .= '</select>';
     	return $result;
     }
 }
?>
