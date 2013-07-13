<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009-2013 Plan.Net France <typo3@plan-net.fr>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/*
 * $Id$
 */

/**
 * Represents the Layar service content reader.
 *
 * @author	Pierrick Caillon <pierrick@in-cite.net>
 * @package	TYPO3
 * @subpackage	ics_layar_service
 */
class tx_icslayarservice_source {
	/**
	 * Constructs the source with the specified layer definition from name.
	 *
	 * @param string $layer The name of the layer to load.
	 * @return void
	 * @throws Exception if layer not found.
	 */
	public function __construct($layer) {
		global $TCA, $TYPO3_DB;
		t3lib_div::loadTCA('tx_icslayarservice_sources');
		$lang = t3lib_div::_GET('L');
		if (empty($lang) || !is_numeric($lang)) {
			$lang = '0';
		}
		// TODO: Change language overlay computation.
		$rows = $TYPO3_DB->exec_SELECTgetRows(
			'`uid`, `sys_language_uid`, `l10n_parent`, `name`, `source`, `page`, `title`, `line2_ts`, `line3_ts`, `line4_ts`, `attribution_ts`, `actions_ts`, `actions_label_ts`, `image`, `type`, `coordinates`',
			'`tx_icslayarservice_sources`',
			'`name` = ' . $TYPO3_DB->fullQuoteStr($layer, 'tx_icslayarservice_sources') . ' ' .
			'AND `sys_language_uid` IN (-1, 0) ' .
			$GLOBALS['TSFE']->cObj->enableFields('tx_icslayarservice_sources'),
			'',
			'sys_language_uid'
		);
		if (!empty($rows)) {
			t3lib_div::loadTCA('tx_icslayarservice_sources');
			$this->layer = $GLOBALS['TSFE']->sys_page->getRecordOverlay('tx_icslayarservice_sources', $rows[0], $lang);
		}
		if ($this->layer) {
			foreach ($TCA['tx_icslayarservice_sources']['columns'] as $field => $conf) {
				$required = ($conf['config']['minitems'] > 0) ||
					(in_array('required', t3lib_div::trimExplode(',', $conf['config']['eval'], true)));
				if ($required && !strcmp($this->layer[$field], '')) {
					throw new Exception('Invalid Layer.');
				}
			}
		}
		else
			throw new Exception('Layer could not be found.');
	}

	/**
	 * Defines the standard filters.
	 *
	 * @param double $latitude Request center latitude (degres).
	 * @param double $longitude Request center longitude (degres).
	 * @param double $range Search range (in meters).
	 * @return void
	 */
	public function setFilter($latitude, $longitude, $range) {
		$this->latitude = floatval($latitude);
		$this->longitude = floatval($longitude);
		$this->range = intval($range);
	}

	/**
	 * Retrieves and transforms for output the POIs.
	 *
	 * @return array All the requested POIs.
	 */
	public function getPOIs() {
		$table = $this->layer['source'];
		$pid = $this->layer['page'];
		$coords = $this->layer['coordinates'];
		$coords = t3lib_div::trimExplode(',', $coords, true);
		$coordAccess = array();
		switch (count($coords)) {
			case 1:
				$coordAccess = array(
					'SUBSTRING_INDEX(' . $coords[0] . ', \',\', 1)',
					'SUBSTRING_INDEX(' . $coords[0] . ', \',\', -1)',
				);
				break;
			case 2:
				$coordAccess = $coords;
				break;
			default:
				return array();
		}
		$alphaRange = rad2deg($this->range / 6378137); // TODO: Custom filters support. // TODO: Search only default and all languages.
		$box = '(' . implode(' AND ', array(
				$this->_makeRangeCriteria($coordAccess[0], $this->latitude - $alphaRange, $this->latitude + $alphaRange, -90, 90, false),
				$this->_makeRangeCriteria($coordAccess[1], $this->longitude - $alphaRange, $this->longitude + $alphaRange, -180, 180, true),
			)) . ') ';
		$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'*, ' . $this->latitude . ' AS center_lat, ' . $this->longitude . ' AS center_lon',
			$table,
			(($pid) ? ('pid = ' . $pid . ' AND ') : ('')) . $box . $GLOBALS['TSFE']->cObj->enableFields($table)
		);
		if (empty($rows)) {
			return array();
		}
		// TODO: Version suppport.
		$transformation = t3lib_div::makeInstance('tx_icslayarservice_transformation', $this->layer);
		$pois = array();
		foreach ($rows as $row) {// TODO: localization support for each row.
			$pois[] = $transformation->transformPOI($row);
		}
		return $pois;
	}
	
	/**
	 * Buils a SQL criteria for the specified values limited to their ranges.
	 *
	 * @param string $field The SQL field definition to use. Not protected. Can be an expression.
	 * @param double $from The searched range lower value.
	 * @param double $to The searched range upper value.
	 * @param double $min The searched values range lower bound.
	 * @param double $max The searched values range upper bound.
	 * @param boolean $normalize Wheither to do a normalization on the searched values. See remarks.
	 * @return array All the requested POIs.
	 * @remarks
	 * If $normalize is true, $from and $to values are passed through _normalize().
	 * If $normalize is false, $min and $max are sets as the boundaries values for $from and $to.
	 */
	private function _makeRangeCriteria($field, $from, $to, $min, $max, $normalize) {
		if ($normalize) {
			$from = $this->_normalize($from, $min, $max);
			$to = $this->_normalize($to, $min, $max);
		}
		else {
			$from = max($min, min($max, $from));
			$to = max($min, min($max, $to));
		}
		if ($from == $to) {
			return '(' . $field . ' = ' . $from . ')';
		}
		else if ($from < $to) {
			return '(' . $field . ' BETWEEN ' . $from . ' AND ' . $to . ')';
		}
		else {
			return '((' . $field . ' BETWEEN ' . $min . ' AND ' . $to . ') OR ' .
				'(' . $field . ' BETWEEN ' . $from . ' AND ' . $max . '))';
		}
	}

	/**
	 * Normalizes a value to the specified range.
	 *
	 * @param double $value The value to normalize.
	 * @param double $min The range lower bound.
	 * @param double $max The range upper upper.
	 * @return double The normalized value.
	 */
	private function _normalize($value, $min, $max) {
		$range = $max - $min;
		while ($value < $min) {
			$value += $range;
		}
		while ($value > $max) {
			$value -= $range;
		}
		return $value;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ics_layar_service/Classes/class.tx_icslayarservice_source.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ics_layar_service/Classes/class.tx_icslayarservice_source.php']);
}
