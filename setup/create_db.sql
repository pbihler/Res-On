SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


-- --------------------------------------------------------


DROP TABLE IF EXISTS `projects`;
CREATE TABLE IF NOT EXISTS `projects` (
  `project_id` int(11) unsigned NOT NULL auto_increment,
  `project_name` varchar(255) character set latin1 collate latin1_german1_ci NOT NULL,
  `project_pwd` varchar(255) character set latin1 collate latin1_german1_ci NOT NULL,
  `project_pdf_introduction` text,
  `project_pdf_hint` text,
  PRIMARY KEY  (`project_id`),
  UNIQUE KEY `project_name` (`project_name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


INSERT INTO `projects` (`project_id`, `project_name`, `project_pwd`, `project_pdf_introduction`, `project_pdf_hint`) VALUES 
(1, 'Example Examination', '*94BDCEBE19083CE2A1F959FD02F964C7AF4CFC29', 'For your convenience, this exam supports the online access to the final results using <i>Res-On</i>.\r\n<br>To enable the possibility to acces your results online, please copy the R-Key <b>%s</b> to the cover page of your exam. Please <b>do not write your password anywhere on the exam</b> since this will prohibit access to your results via <i>Res-On</i>.', '<i>To access your results, please visit <b>%s</b> and enter your matriculation number and the password provided above</i>.\r\n<br><br><b>Information:</b> By copying your R-Key to your exam cover sheet, you agree, that your result will be stored encrypted in a database and is accessible online to everyone knowing your matriculation number and the password above. If you do not agree, just ignore this paper and do not copy the R-Key to your exam.');

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