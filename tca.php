<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA['tx_mklistbydate_entries'] = array (
	'ctrl' => $TCA['tx_mklistbydate_entries']['ctrl'],
	'interface' => array (
		'showRecordFieldList' => 'hidden,starttime,endtime,text,weekdays,occurrence,dayofmonth'
	),
	'feInterface' => $TCA['tx_mklistbydate_entries']['feInterface'],
	'columns' => array (
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'starttime' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.starttime',
			'config'  => array (
				'type'     => 'input',
				'size'     => '8',
				'max'      => '20',
				'eval'     => 'date',
				'default'  => '0',
				'checkbox' => '0'
			)
		),
		'endtime' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.endtime',
			'config'  => array (
				'type'     => 'input',
				'size'     => '8',
				'max'      => '20',
				'eval'     => 'date',
				'checkbox' => '0',
				'default'  => '0',
				'range'    => array (
					'upper' => mktime(3, 14, 7, 1, 19, 2038),
					'lower' => mktime(0, 0, 0, date('m')-1, date('d'), date('Y'))
				)
			)
		),
		'text' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:mk_listbydate/locallang_db.xml:tx_mklistbydate_entries.text',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',	
				'eval' => 'required',
			)
		),
		'weekdays' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:mk_listbydate/locallang_db.xml:tx_mklistbydate_entries.weekdays',		
			'config' => array (
				'type' => 'check',
				'cols' => 4,
				'items' => array (
					array('LLL:EXT:mk_listbydate/locallang_db.xml:tx_mklistbydate_entries.weekdays.I.0', ''),
					array('LLL:EXT:mk_listbydate/locallang_db.xml:tx_mklistbydate_entries.weekdays.I.1', ''),
					array('LLL:EXT:mk_listbydate/locallang_db.xml:tx_mklistbydate_entries.weekdays.I.2', ''),
					array('LLL:EXT:mk_listbydate/locallang_db.xml:tx_mklistbydate_entries.weekdays.I.3', ''),
					array('LLL:EXT:mk_listbydate/locallang_db.xml:tx_mklistbydate_entries.weekdays.I.4', ''),
					array('LLL:EXT:mk_listbydate/locallang_db.xml:tx_mklistbydate_entries.weekdays.I.5', ''),
					array('LLL:EXT:mk_listbydate/locallang_db.xml:tx_mklistbydate_entries.weekdays.I.6', ''),
				),
			)
		),
		'occurrence' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:mk_listbydate/locallang_db.xml:tx_mklistbydate_entries.occurrence',		
			'config' => array (
				'type' => 'check',
				'cols' => 4,
				'items' => array (
					array('LLL:EXT:mk_listbydate/locallang_db.xml:tx_mklistbydate_entries.occurrence.I.0', ''),
					array('LLL:EXT:mk_listbydate/locallang_db.xml:tx_mklistbydate_entries.occurrence.I.1', ''),
					array('LLL:EXT:mk_listbydate/locallang_db.xml:tx_mklistbydate_entries.occurrence.I.2', ''),
					array('LLL:EXT:mk_listbydate/locallang_db.xml:tx_mklistbydate_entries.occurrence.I.3', ''),
					array('LLL:EXT:mk_listbydate/locallang_db.xml:tx_mklistbydate_entries.occurrence.I.4', ''),
				),
			)
		),
		'dayofmonth' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:mk_listbydate/locallang_db.xml:tx_mklistbydate_entries.dayofmonth',		
			'config' => array (
				'type'     => 'input',
				'size'     => '4',
				'max'      => '4',
				'eval'     => 'int',
				'checkbox' => '0',
				'range'    => array (
					'upper' => '31',
					'lower' => '1'
				),
				'default' => 1
			)
		),
	),
	'types' => array (
		'0' => array('showitem' => 'hidden;;1;;1-1-1, text, weekdays, occurrence, dayofmonth')
	),
	'palettes' => array (
		'1' => array('showitem' => 'starttime, endtime')
	)
);
?>