SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


-- --------------------------------------------------------


DROP TABLE IF EXISTS `projects`;
CREATE TABLE IF NOT EXISTS `projects` (
  `project_id` int(11) unsigned NOT NULL auto_increment,
  `project_name` varchar(255) character set latin1 collate latin1_german1_ci NOT NULL,
  `project_pwd` varchar(255) character set latin1 collate latin1_german1_ci NOT NULL,
  PRIMARY KEY  (`project_id`),
  UNIQUE KEY `project_name` (`project_name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------


DROP TABLE IF EXISTS `results`;
CREATE TABLE IF NOT EXISTS `results` (
  `project_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `mat_no` varchar(255) character set latin1 collate latin1_german1_ci default NULL,
  `result` text character set latin1 collate latin1_german1_ci,
  `crypt_module` varchar(10) character set latin1 collate latin1_german1_ci NOT NULL default 'hash',
  `crypt_data` text character set latin1 collate latin1_german1_ci,
  PRIMARY KEY  (`project_id`,`member_id`),
  KEY `mat_no` (`mat_no`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

