<?php
/*
 * Created on 29.03.2007 by pascal
 *
 * Returns a Administrative Class defined by String in the factory method
 * or a login window, if not logged in.
 * 
 * Therefore, it acts a bit like a Watchdog 
 * 
 * Licenced under GPL: http://www.gnu.org/licenses/gpl.txt
 * 
 */
 abstract class AdminPageFactory {
 	
    /**
     * If the user is logged in, returns the demanded class, otherwise returns a LoginForm
     * @param string type Type of class to create
     * @param string msg a msg to give to the constructor
     * @return Page 
     * 
     */
 	public static function factory($type,$msg = '') {
 		$session = Session::getInstance();
 		if ($session->isLoggedIn()) {
 			return new $type($msg);
 		} else {
 			return new LoginPage($msg);
 		}
 	}
 }
?>
