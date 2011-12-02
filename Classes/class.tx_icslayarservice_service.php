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
 * Answer to a Layar query.
 *
 * @author	Pierrick Caillon <pierrick@in-cite.net>
 * @package	TYPO3
 * @subpackage	ics_layar_service
 */
class tx_icslayarservice_service {
	private $settings;
	
	private static $params = array(
		'layerName' => 'layer',
		'userID' => 'user',
		'developerId' => 'dev',
		'developerHash' => 'hash',
		'timestamp' => 'timestamp',
		'lat' => 'latitude',
		'lon' => 'longitude',
		'radius' => 'range',
		'pageKey' => 'pageKey',
		'countryCode' => 'country',
		'lang' => 'lang',
		'version' => 'version',
	);

	/**
	 * Initializes the service.
	 * Simulates a partial frontend context for TypoScript parsing.
	 *
	 * @return void
	 */
	public function init() {
		$this->settings = array();
		foreach (self::$params as $get => $var) {
			$this->settings[$var] = t3lib_div::_GET($get);
		}

		$TSFE = t3lib_div::makeInstance('tslib_fe', $GLOBALS['TYPO3_CONF_VARS'], 0, 0);
		$GLOBALS['TSFE'] = $TSFE;
		$TSFE->connectToDB();
		$TSFE->initFEuser();
		$TSFE->sys_page = t3lib_div::makeInstance('t3lib_pageSelect');
		$TSFE->sys_page->init(false);
		$TSFE->getCompressedTCarray();
		$TSFE->initTemplate();
		$TSFE->lang = $this->settings['lang'] ? strtolower($this->settings['lang']) : 'default';
		$TSFE->renderCharset = $TSFE->csConvObj->parse_charset($GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'] ? $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'] : $TSFE->defaultCharSet);
		$TSFE->metaCharset = $this->renderCharset;
		$TSFE->newCObj();
	}
	
	/**
	 * Obtains the value of an attribute.
	 *
	 * @param string $name Attribute name.
	 * @return string The attribute's value.
	 */
	public function __get($name) {
		if (isset($this->settings[$name])) {
			return $this->settings[$name];
		}
		return '';
	}

	/**
	 * Entry point of the service.
	 *
	 * @return string The output content.
	 */
	public function main() {
		$result = array(
			'nextPageKey' => '',
			'morePages' => false,
			'hotspots' => array(),
			'layer' => $this->layer,
			'errorCode' => 0,
			'errorString' => 'ok',
		);
		// Ask next page use case.
		if (!empty($this->pageKey)) {
			$this->_readPage($result);
		}
		// Ask layer data use case.
		else {
			$this->_loadPOIs($result);
		}
		$content = json_encode($result/*, JSON_HEX_TAG*/);
		return $content;
	}

	/**
	 * Builds result from a saved page.
	 *
	 * @param array &$result The result array to modify.
	 * @return void
	 */
	private function _readPage(array &$result) {
		$pager = t3lib_div::makeInstance('tx_icslayarservice_pager');
		if ($pager->load($this->pageKey)) {
			// Valid pageKey.
			$result['hotspots'] = $pager->getPage();
			$result['nextPageKey'] = $pager->getKey();
			$result['morePages'] = $result['nextPageKey'] != '';
		}
		else {
			// Unknown pageKey.
			$result['errorCode'] = 29;
			$result['errorString'] = 'Page key invalid';
		}
	}
	
	/**
	 * Builds result for a new request.
	 *
	 * @param array &$result The result array to modify.
	 * @return void
	 */
	private function _loadPOIs(array &$result) {
		if ($source = $this->_openSource()) {
			// Valid layer.
			$source->setFilter($this->latitude, $this->longitude, $this->range); // TODO: Custom filters support.
			$pois = $source->getPOIs();
			if (!empty($pois)) {
				$pager = t3lib_div::makeInstance('tx_icslayarservice_pager');
				$pager->start($pois);
				$result['hotspots'] = $pager->getPage();
				$result['nextPageKey'] = $pager->getKey();
				$result['morePages'] = $result['nextPageKey'] != '';
			}
			else {
				// Empty result. Do nothing.
			}
		}
		else {
			// Unknown layer.
			$result['errorCode'] = 20;
			$result['errorString'] = 'The layer name is not known.';
		}
	}
	
	/**
	 * Instanciates a source class for the requested layer.
	 *
	 * @return tx_icslayarservice_source The source instance.
	 */
	private function _openSource() {
		try {
			// TODO: Version support (v2.1 - 5.0 source and v6.0+ sources)
			$source = t3lib_div::makeInstance('tx_icslayarservice_source', $this->layer);
			return $source;
		}
		catch (Exception $e) {
			return false;
		}
	}

	/**
	 * Prints the given JSon content.
	 *
	 * @param string $output Content to write.
	 * @return void
	 */
	public function printOutput($output) {
		global $TYPO3_CONF_VARS;
		header('Content-Type: application/json; charset=' . (($TYPO3_CONF_VARS['BE']['forceCharset']) ? ($TYPO3_CONF_VARS['BE']['forceCharset']) : ('iso-8859-1')));
		header('Content-Length: ' . strlen($output));
		echo $output;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ics_layar_service/Classes/class.tx_icslayarservice_service.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ics_layar_service/Classes/class.tx_icslayarservice_service.php']);
}
