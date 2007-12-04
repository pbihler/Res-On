<?php
/*
 * Created on 21.03.2007
 *
 * Abstract class
 * 
 * Contains the main features of a Res-On page
 * 
 * Licenced under GPL: http://www.gnu.org/licenses/gpl.txt
 * 
 */
 
 abstract class Page {
     
     protected $page_title = "Res-On";
     // To add something to the menu, use $this->menu = array("caption"=>"link") + $this->menu; 
     protected $menu = array();
     
     protected $introduction = '';
     
     function __construct() {
         
         // Check if SSL is required, maybe redirect
         if (Config::$require_ssl  && $_SERVER['HTTPS'] != "on") {
            $url = "https://". $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
    		header("Location: $url");
    		exit;
         }
         
         //Set language
         $lang = $this->getLanguageFromBrowser();
         Messages::setLanguage($lang);
         
         if (Config::$contact_url) 
             $this->menu += array(Messages::getString('Page.Contact') => Config::$contact_url);
             
             
     }
     
     /**
      * Tries to get the selected language from the browser (simple heuristic)
      */
      private function getLanguageFromBrowser() {
      	
        $available_languages = array_keys(Config::$languages);
        
        if (($selected_lang = $_GET['l']) && (in_array($selected_lang,$available_languages))) {
        	// Take language selected via GET
        	setcookie('language',$selected_lang,0,'/',null,Config::$require_ssl);
        } elseif (($selected_lang = $_COOKIE['language']) && (in_array($selected_lang,$available_languages))) {
        	// Take language from cookie
        } else {
	      	$browser_languages = $_SERVER["HTTP_ACCEPT_LANGUAGE"];
	      	$selected_lang = "";
	      	$selected_pos = strlen($browser_languages);
	        foreach ($available_languages as $lang) {
	        	$pos = strpos($browser_languages,$lang);
	        	if (($pos !== false) && ($pos < $selected_pos)) {
	        		$selected_pos = $pos;
	        	    $selected_lang = $lang;
	        	}
	        }
        }
        return $selected_lang;
      }
     
     /*
      * Renders the content of the page 
      */
     public function render() {
         $this->renderHeader();
         $this->renderPageContent();
         $this->renderFooter();
     }
     
     /**
      * Override this function to write some other page style than just notes
      */
     protected function renderPageContent() {
         echo "    <span id=\"introduction\">"; 
	     if ($this->introduction) echo $this->introduction;
	     echo "</span>\n";
	     
         echo "    <div id=\"wrapperContent\">\n";
         $this->renderNotes();
         
         // Show disclaimer
         if (Config::$disclaimer['text'] || Config::$disclaimer['title'])
         	$this->renderNote(Config::$disclaimer['text'],Config::$disclaimer['title']);
         echo "    </div>\n";
     }
     
     protected function setTitle($title) {
     	 $this->page_title = $title; 
     }
     
     protected function renderNotes() {
         /*
          * to render several notes, override this function with something like:
          * 
          * $this->renderNote('Content','Title','Date');
          * $this->renderNote('Another Content','Another Title','Another Date');
          * ...
          */
     }
     
     
     
    /**
     * 
     * Now, there follows the frightening "HTML and PHP-code-mixed" section:
     * (This is not quite needed, but makes the code easier to maintain. Really.) 
     * 
     **/
     
     
     /*
      * To produce valid html, render_footer has to be called after this
      */
     private function renderHeader() {
     	global $PHP_SELF;
         ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?php echo $this->page_title ?> - Res-On</title>
<link href="format/style.css" rel="stylesheet" type="text/css" />
</head>

<body>

<div id="wrapper">

<div id="header">
<h1><?php echo Info::$formated_title; ?></h1>
<h3><?php echo $this->page_title ?></h3>
</div>

<div id="menu">
<?php foreach ($this->menu as $caption => $link) { ?> 
      	<a href="<?php echo $link; ?>" class="link"><?php echo $caption; ?></a>
<?php } ?>
<div id="language_bar"><?php 
    
    foreach (Config::$languages as $lang_code => $lang_info) {
    	echo sprintf('<a href="%1$s?l=%2$s"><img src="format/%3$s" alt="%4$s" title="%4$s" %5$s /></a>',
    	    $PHP_SELF,
    	    $lang_code,
    	    $lang_info['icon'],
    	    $lang_info['name'],
    	    $lang_code == Messages::getLanguage() ? 'class="sel"' : '');
    }
    
    ?></div> 
</div>
        
<div id="contentwrapper"><div id="content">
    
  
         
         <?php
     }
     
     private function renderFooter() {
         
         ?>
<!-- <div id="unilogo">&nbsp;</div> -->
</div></div>
<div id="footer">
<?php echo Info::footer(); ?>
</div>

</div>

</body>
</html>
		<?php
         
     }
     
     protected function writeJavascript($script) {
         echo  "\n" .'<script type="text/javascript" language="javascript">' . "\n" .
         		'  <!--' .  "\n" .
         		$script .  "\n" .
         		'  // -->' .  "\n" .
         		'</script>' . "\n";
     }
     
     protected function formatError($message) {
         return '<div class="error">' . $message . "</div><br />\n";
     }
     
     protected function renderNote($text, $title = '') {
         ?>
         <?php if ($title) { ?>
            <h3><?php echo $title; ?></h3>
          <?php } ?>
		<p>
          <?php echo $text; ?>
		</p>
         <?php
     }
     
     protected function renderError($error,$back = true) {
     	$text = sprintf('<div class="error">%s</div>',$error);
     	if ($back)
             $this->renderBackNote($text,Messages::getString('General.Error'));         
        else
             $this->renderNote($text,Messages::getString('General.Error'));        
     }
     
     protected function renderBackNote($text, $title = '') {
             $this->renderNote(sprintf('%s' .
             		'<div class="back"><input type="button" value="%s" onclick="history.back();" /></div>',$text,Messages::getString('General.Back')),$title);         
     }
      
 
 }
 
?>
