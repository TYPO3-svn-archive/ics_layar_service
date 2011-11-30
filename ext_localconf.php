<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TYPO3_CONF_VARS['FE']['eID_include']['layar'] = 'EXT:' . $_EXTKEY . '/eID/tx_icslayarservice_service.php';
?>
