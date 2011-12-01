<?php

########################################################################
# Extension Manager/Repository config file for ext "ics_layar_service".
#
# Auto generated 01-12-2011 10:44
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Generic Layar Service Provider',
	'description' => 'Provides a Layar Service Provider for custom configurable layer Content Source. Layar is an augmented reality engine for last generation mobile phones. See http://layar.pbworks.com/ for more information about Layar. Support only API v2.1.',
	'category' => 'fe',
	'author' => 'In Cite Solution',
	'author_email' => 'technique@in-cite.net',
	'shy' => '',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'beta',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author_company' => 'In Cite Solution',
	'version' => '0.1.1',
	'constraints' => array(
		'depends' => array(
			'typo3' => '4.3.0-0.0.0',
			'php' => '5.2.0-0.0.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:18:{s:9:"ChangeLog";s:4:"ced4";s:10:"README.txt";s:4:"ee2d";s:16:"ext_autoload.php";s:4:"234e";s:12:"ext_icon.gif";s:4:"1bdc";s:17:"ext_localconf.php";s:4:"bb79";s:14:"ext_tables.php";s:4:"f9b7";s:14:"ext_tables.sql";s:4:"644e";s:35:"icon_tx_icslayarservice_sources.gif";s:4:"475a";s:25:"locallang_csh_sources.xml";s:4:"4128";s:16:"locallang_db.xml";s:4:"e6a2";s:7:"tca.php";s:4:"8e7a";s:42:"Classes/class.tx_icslayarservice_pager.php";s:4:"b04c";s:44:"Classes/class.tx_icslayarservice_service.php";s:4:"2a62";s:43:"Classes/class.tx_icslayarservice_source.php";s:4:"f87f";s:51:"Classes/class.tx_icslayarservice_transformation.php";s:4:"a267";s:25:"doc/ics_layar_service.vsd";s:4:"4f07";s:14:"doc/manual.sxw";s:4:"72e5";s:34:"eID/tx_icslayarservice_service.php";s:4:"e6a5";}',
	'suggests' => array(
	),
);

?>