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
         $this->page_title = "Introduction"; 
         $this->introduction = "Introduction"; 
     }
     
     function renderNotes() {
         $this->renderNote('Content','Title','Date');
         $cr = new CryptProxy();
         $pwd_gen = new ConfiguredPasswordGenerator();
         echo $pwd = $pwd_gen->generatePassword();
         echo "<br />";
         echo $cd = $cr->generateCryptData($pwd);
         echo "<br />";
         echo $rd = $cr->encryptResult("heimlich",$cd);
         echo "<br />";
         echo $cr->decryptResult($rd,$cd,$pwd);
         $this->renderNote('Another Content','Another Title','Another Date');
     }
     
 }
?>
