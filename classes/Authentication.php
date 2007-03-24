<?php
/*
 * Created on 24.03.2007 by bihler
 *
 * Handles the authentication for administration
 * 
 * Licenced under GPL: http://www.gnu.org/licenses/gpl.txt
 *
 */
 class Authentication {
     static function authenticate($password) {
         return $password == 'bla'; // No real authentication yet
     }
 }
?>
