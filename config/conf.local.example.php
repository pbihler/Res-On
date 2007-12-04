<?php
/**
 * 
 * Rename to "conf.local.php" to enable - details about the parameter are to be found in conf.php
 * 
 * WARNING:
 * 
 * Please make sure, that this file can only be read by the webserver,
 * and that the directory containing this file contains a .htaccess
 * denying web-access to it!
 * 
 */

// Local configuration (overwrites defaults)
//Config::$require_ssl = true;
//Config::$default_crypt_module = "gpg";
Config::$crypt_info['hash']['salt'] = '$2$kd(jmlokDK8kl'; //Change this (length: 16 characters, starting wit "$2$")
//Config::$crypt_info['gpg']['program_path'] = '/usr/bin/gpg';
//Config::$database['server'] = '';
Config::$database['username'] = 'reson_access';
Config::$database['password'] = 'j/fsOv2,';
//Config::$database['database'] = '';
//Config::$database['table_prefix'] = '';
//Config::$contact_url = 'http://www.example.com/contact';
//Config::$pdf_settings['orientation'] = 'P';
//Config::$pdf_settings['format'] = 'A5';
//Config::$default_project_id = 1;
Config::$master_password = "CreateProjectPassword";