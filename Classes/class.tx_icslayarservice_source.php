<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009-2011 In Cité Solution <technique@in-cite.net>
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
	 * Initializes the source. Nothing to do.
	 *
	 * @return void
	 */
	public function init() {
		$GLOBALS['TSFE'] = new stdClass();
	}

	/**
	 * Loads a layer definition from name.
	 *
	 * @param string $layer The name of the layer to load.
	 * @return boolean The success of the operation.
	 */
	public function loadLayer($layer) {
		global $TCA;
		t3lib_div::loadTCA('tx_icslayarservice_sources');
		$lang = t3lib_div::_GET('L');
		if (empty($lang) || !is_numeric($lang)) {
			$lang = '0';
		}
		// TODO: Change language overlay computation.
		$rows = $TYPO3_DB->exec_SELECTgetRows(
			'`uid`, `sys_language_uid`, `l10n_parent`, `name`, `source`, `page`, `title`, `line2_ts`, `line3_ts`, `line4_ts`, `attribution_ts`, `actions`, `actions_label`, `image`, `type`, `coordinates`',
			'`tx_icslayarservice_sources`',
			'`name` = ' . $TYPO3_DB->fullQuoteStr($layer, 'tx_icslayarservice_sources') . ' ' .
			'AND `sys_language_uid` IN (-1, 0) ' .
			'AND `hidden` = 0 AND `deleted` = 0',
			'',
			'',
			'',
			'sys_language_uid'
		);
		if (!empty($rows)) {
			if (isset($rows[$lang])) {
				$this->layer = $rows[$lang];
				$rows = $TYPO3_DB->exec_SELECTgetRows(
					'`uid`, `sys_language_uid`, `l10n_parent`, `name`, `source`, `page`, `title`, `line2_ts`, `line3_ts`, `line4_ts`, `attribution_ts`, `actions`, `actions_label`, `image`, `type`, `coordinates`',
					'`tx_icslayarservice_sources`',
					'`uid` = ' . $this->layer['l10n_parent'] . ' ' .
					'AND `hidden` = 0 AND `deleted` = 0',
					'',
					'',
					'1'
				);
				if (!empty($rows)) {
					foreach ($rows[0] as $field => $value) {
						$mode = $TCA['tx_icslayarservice_sources']['columns'][$field]['l10n_mode'];
						if (($mode != 'exclude') &&
							(($mode != 'mergeIfNotBlank') || strcmp(trim($this->layer[$field]), '')))
							$rows[0][$field] = $this->layer[$field];
					}
					$this->layer = $rows[0];
				}
			}
			elseif (isset($rows['-1'])) {
				$this->layer = $rows['-1'];
			}
			else {
				$this->layer = $rows['0'];
			}
			if (($this->layer['sys_language_uid'] <= 0) && ($lang > 0)) {
				$rows = $TYPO3_DB->exec_SELECTgetRows(
					'`uid`, `sys_language_uid`, `l10n_parent`, `name`, `source`, `page`, `title`, `line2_ts`, `line3_ts`, `line4_ts`, `attribution_ts`, `actions`, `actions_label`, `image`, `type`, `coordinates`',
					'`tx_icslayarservice_sources`',
					'`l10n_parent` = ' . $this->layer['uid'] . ' ' .
					'AND `sys_language_uid` IN (' . $lang . ') ' .
					'AND `hidden` = 0 AND `deleted` = 0',
					'',
					'',
					'1'
				);
				if (!empty($rows)) {
					foreach ($this->layer as $field => $value) {
						$mode = $TCA['tx_icslayarservice_sources']['columns'][$field]['l10n_mode'];
						if (($mode != 'exclude') &&
							(($mode != 'mergeIfNotBlank') || strcmp(trim($rows[0][$field]), '')))
							$this->layer[$field] = $rows[0][$field];
					}
				}
			}
		}
		if ($this->layer) {
			foreach ($TCA['tx_icslayarservice_sources']['columns'] as $field => $conf) {
				$required = ($conf['config']['minitems'] > 0) ||
					(in_array('required', t3lib_div::trimExplode(',', $conf['config']['eval'], true)));
				if ($required && !strcmp($this->layer[$field], '')) {
					return false;
				}
			}
			return true;
		}
		return false;
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
				// TODO: Case when the coordinates are in the same field.
				break;
			case 2:
				$coordAccess = $coords;
				break;
			default:
				return array();
		}
		$alphaRange = rad2deg($this->range / 6378137);
		// TODO: When the latitude go below -90 or above 90.
		$box = '((' . $coordAccess[0] . ' BETWEEN ' . ($this->latitude - $alphaRange) . ' AND ' . ($this->latitude + $alphaRange) . ') '.
		// TODO: When the longitude go below -180 or above 180.
			' AND (' . $coordAccess[1] . ' BETWEEN ' . ($this->longitude - $alphaRange) . ' AND ' . ($this->longitude + $alphaRange) . ')) ';
		$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'*, ' . $this->latitude . ' AS center_lat, ' . $this->longitude . ' AS center_lon',
			$table,
			// TODO: Use cObj->enableFields.
			(($pid) ? ('pid = ' . $pid . ' AND ') : ('')) . $box . ' AND hidden = 0 AND deleted = 0'
		);
		if (empty($rows)) {
			return array();
		}
		$transformation = t3lib_div::makeInstance('tx_icslayarservice_transformation');
		$transformation->init($this->layer);
		$pois = array();
		foreach ($rows as $row) {
			$pois[] = $transformation->transformPOI($row);
		}
		return $pois;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ics_layar_service/Classes/class.tx_icslayarservice_source.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ics_layar_service/Classes/class.tx_icslayarservice_source.php']);
}
