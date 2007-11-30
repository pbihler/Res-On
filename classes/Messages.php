<?php
/*
 * Created on 30.11.2007 by bihler
 *
 * This file supports externalisation of Strings and I18N
 * 
 * Licenced under GPL: http://www.gnu.org/licenses/gpl.txt
 *
 */
 
 
final class Messages {
	
	private static $DEFAULT_LANGUAGE = 'en';
	private static $LANG_FILE = "lang/%s.properties";
	
	
	private static $selected_language = "";
	private static $props = null;
	
	public static function getString($key) {
		if (! self::$props)
		   self::readProps();
		   
		$result = self::$props->getProperty($key);
		return $result ? $result : '!' . $key . '!';
	}
	
	public static function setLanguage($language) {
		if ($language != self::$selected_language) {
			self::$selected_language = $language;
			self::readProps();
		}
	}
	
	public static function getLanguage() {
		 return self::$selected_language  ? self::$selected_language : self::$DEFAULT_LANGUAGE;
	}
	
	private static function readProps() {
		$propfile = sprintf(self::$LANG_FILE,self::$selected_language);
		if (! file_exists($propfile)) 
			$propfile = sprintf(self::$LANG_FILE,self::$DEFAULT_LANGUAGE);
			
		self::$props = new Properties();
		    
		if ($propfile_content = file_get_contents($propfile)) {
			self::$props->load($propfile_content);
		}
	}
	
 }
?>
