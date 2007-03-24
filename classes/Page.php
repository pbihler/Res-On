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
         if (MainConfig::$require_ssl  && $_SERVER['HTTPS'] != "on") {
            $url = "https://". $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
    		header("Location: $url");
    		exit;
         }
         
         if (MainConfig::$contact_url) 
             $this->menu += array('Contact' => MainConfig::$contact_url);
             
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
	     if ($this->introduction) 
         	echo "    <div id=\"introduction\">" . $this->introduction . "</div>\n";
         echo "    <div id=\"wrapperContent\">\n";
         $this->renderNotes();
         echo "    </div>\n";
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
         ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <link rel="stylesheet" href="format/style.css" type="text/css" />
  <title><?php echo $this->page_title ?></title>
</head>
<body>

<div id="wrapperAll">
	
  <div id="content">
	
    <div id="topWrapper">
      <div id="logo"><?php echo Info::$formated_title; ?></div>
      <div id="menu">
       <ul>
       <?php foreach ($this->menu as $caption => $link) { ?>
       	<li>&raquo;&nbsp;<a href="<?php echo $link; ?>"><?php echo $caption; ?></a></li>
       <?php } ?>
       </ul>
      </div>
    </div>
    
    <div id="sep">&nbsp;</div>
         
         <?php
     }
     
     private function renderFooter() {
         
         ?>
</div>
  
  <div id="bottom">
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
     
     protected function renderNote($text, $title = '', $date = '') {
         ?>
      <div id="note">
        <div id="noteTop">
          &nbsp;
        </div>
        <div id="noteContent">
          <?php if ($title) { ?>
            <div id="title"><?php echo $title; ?></div>
          <?php } ?>
          <?php echo $text; ?>
          <?php if ($date) { ?>
             <div id="date"><?php echo $date; ?></div>
          <?php } ?>
          
        </div>
        <div id="noteBottom">
          &nbsp;
        </div>
      </div>
         <?php
     }
      
 
 }
 
?>
