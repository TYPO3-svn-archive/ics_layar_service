<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA['tx_icslayarservice_sources'] = array (
	'ctrl' => $TCA['tx_icslayarservice_sources']['ctrl'],
	'interface' => array (
		'showRecordFieldList' => 'sys_language_uid,l10n_parent,l10n_diffsource,hidden,name,source,page,title,line2_ts,line3_ts,line4_ts,attribution_ts,actions,actions_label,image,type,coordinates'
	),
	'feInterface' => $TCA['tx_icslayarservice_sources']['feInterface'],
	'columns' => array (
		'sys_language_uid' => array (		
			'exclude' => 1,
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.language',
			'config' => array (
				'type'                => 'select',
				'foreign_table'       => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0)
				)
			)
		),
		'l10n_parent' => array (		
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude'     => 1,
			'label'       => 'LLL:EXT:lang/locallang_general.xml:LGL.l18n_parent',
			'config'      => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
				),
				'foreign_table'       => 'tx_icslayarservice_sources',
				'foreign_table_where' => 'AND tx_icslayarservice_sources.pid=###CURRENT_PID### AND tx_icslayarservice_sources.sys_language_uid IN (-1,0)',
			)
		),
		'l10n_diffsource' => array (		
			'config' => array (
				'type' => 'passthrough'
			)
		),
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'name' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ics_layar_service/locallang_db.xml:tx_icslayarservice_sources.name',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',	
				'max' => '30',	
				'eval' => 'required,trim,nospace,unique',
			),
			'l10n_mode' => 'mergeIfNotBlank',
			'l10n_cat' => 'text',
		),
		'source' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ics_layar_service/locallang_db.xml:tx_icslayarservice_sources.source',		
			'config' => array (
				'type' => 'select',	
				'special' => 'tables',
				'minitems' => 1,
				'maxitems' => 1,
				'items' => array(
					0 => array('', ''),
				),
				'size' => '1',	
				'iconsInOptionTags' => 1,
			),
			'l10n_mode' => 'exclude',
			'l10n_display' => 'defaultAsReadonly',
		),
		'page' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ics_layar_service/locallang_db.xml:tx_icslayarservice_sources.page',		
			'config' => array (
				'type' => 'group',	
				'internal_type' => 'db',	
				'allowed' => 'pages',	
				'size' => 1,	
				'minitems' => 0,
				'maxitems' => 1,
			),
			'l10n_mode' => 'exclude',
			'l10n_display' => 'defaultAsReadonly',
		),
		'title' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ics_layar_service/locallang_db.xml:tx_icslayarservice_sources.title',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',	
				'eval' => 'required,trim',
			),
			'l10n_mode' => 'exclude',
			'l10n_display' => 'defaultAsReadonly',
		),
		'line2_ts' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ics_layar_service/locallang_db.xml:tx_icslayarservice_sources.line2_ts',		
			'config' => array (
				'type' => 'text',
				'wrap' => 'OFF',
				'cols' => '48',	
				'rows' => '5',
			),
			'l10n_mode' => 'mergeIfNotBlank',
			'l10n_cat' => 'text',
		),
		'line3_ts' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ics_layar_service/locallang_db.xml:tx_icslayarservice_sources.line3_ts',		
			'config' => array (
				'type' => 'text',
				'wrap' => 'OFF',
				'cols' => '48',	
				'rows' => '5',
			),
			'l10n_mode' => 'mergeIfNotBlank',
			'l10n_cat' => 'text',
		),
		'line4_ts' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ics_layar_service/locallang_db.xml:tx_icslayarservice_sources.line4_ts',		
			'config' => array (
				'type' => 'text',
				'wrap' => 'OFF',
				'cols' => '48',	
				'rows' => '5',
			),
			'l10n_mode' => 'mergeIfNotBlank',
			'l10n_cat' => 'text',
		),
		'attribution_ts' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ics_layar_service/locallang_db.xml:tx_icslayarservice_sources.attribution_ts',		
			'config' => array (
				'type' => 'text',
				'wrap' => 'OFF',
				'cols' => '48',	
				'rows' => '5',
			),
			'l10n_mode' => 'mergeIfNotBlank',
			'l10n_cat' => 'text',
		),
		'actions' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ics_layar_service/locallang_db.xml:tx_icslayarservice_sources.actions',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',
			),
			'l10n_mode' => 'exclude',
			'l10n_display' => 'defaultAsReadonly',
		),
		'actions_label' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ics_layar_service/locallang_db.xml:tx_icslayarservice_sources.actions_label',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',
			),
			'l10n_mode' => 'exclude',
			'l10n_display' => 'defaultAsReadonly',
		),
		'image' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ics_layar_service/locallang_db.xml:tx_icslayarservice_sources.image',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',	
				'eval' => 'trim',
			),
			'l10n_mode' => 'exclude',
			'l10n_display' => 'defaultAsReadonly',
		),
		'type' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ics_layar_service/locallang_db.xml:tx_icslayarservice_sources.type',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',	
				'eval' => 'trim',
			),
			'l10n_mode' => 'exclude',
			'l10n_display' => 'defaultAsReadonly',
		),
		'coordinates' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ics_layar_service/locallang_db.xml:tx_icslayarservice_sources.coordinates',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',
				'eval' => 'required',
			),
			'l10n_mode' => 'exclude',
			'l10n_display' => 'defaultAsReadonly',
		),
	),
	'types' => array (
		'0' => array('showitem' => 'sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, hidden;;1, name, source, page, title;;;;2-2-2, line2_ts;;;;3-3-3, line3_ts, line4_ts, attribution_ts, actions, actions_label, image, type, coordinates')
	),
	'palettes' => array (
		'1' => array('showitem' => '')
	)
);
?>
