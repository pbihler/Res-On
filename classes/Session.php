<?php
/*
 * Created on 24.03.2007 by bihler
 *
 * This contains page-persitant data (and is a Singleton)
 * 
 * Licenced under GPL: http://www.gnu.org/licenses/gpl.txt
 *
 */
 
 class Session extends Singleton {
   
     private $logged_in = false;
     
     private static $SESSION_IDENTIFIER = 'ResOn_Session';
  
     
     public function isLoggedIn() {
         return $this->logged_in;
     }
     
     /**
      * Returns the singleton instance of Session
      */
     public static function getInstance(){
         session_start();
         // Default object is the one stored in the $_SESSION-variable (if existant)
         $object_registered = isset($_SESSION[self::$SESSION_IDENTIFIER]);
         $session = Singleton::getSingleton(get_class(), $object_registered ? $_SESSION[self::$SESSION_IDENTIFIER] : null);
         
         if (! $object_registered)
	     	 //save singleton object in session:
	     	 $_SESSION[self::$SESSION_IDENTIFIER] = $session;

         return $session;
     }
     
     /**
      * Creates a new Session-oject, if password is correct
      */
     static function createNewSession($password) {
        
        // Delete old Session object from php_session cache
        // and create new seesion_id to prevent session fixation:
        self::destroySession();
        $session = self::getInstance();
        $auth_ok = Authentication::authenticate($password);
	 	if ($auth_ok) {
	 	    $session->logged_in = true;
	 	} 
	 	return $session;
     }
     
     static function destroySession() {
         
		//Destroy old session, if needed
		if (session_name()!='') {
			session_start();
        	session_destroy();
		}
		
        session_regenerate_id(true);
        session_start();
       

        // PHP < 4.3.3, since it does not put
        setcookie(session_name(), session_id());
     }
      
 }
?>
