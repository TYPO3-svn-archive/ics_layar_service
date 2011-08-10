<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}
$TCA['tx_icslayarservice_sources'] = array (
	'ctrl' => array (
		'title'     => 'LLL:EXT:ics_layar_service/locallang_db.xml:tx_icslayarservice_sources',
		'label'     => 'name',
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'languageField'            => 'sys_language_uid',
		'transOrigPointerField'    => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'default_sortby' => 'ORDER BY name',
		'delete' => 'deleted',
		'enablecolumns' => array (
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_icslayarservice_sources.gif',
		'rootLevel' => -1,
	),
);

t3lib_extMgm::addLLrefForTCAdescr('tx_icslayarservice_sources', 'EXT:ics_layar_service/locallang_csh_sources.xml');
?>
