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
 * Represents the Layar service content transformer from database to POI.
 *
 * @author	Pierrick Caillon <pierrick@in-cite.net>
 * @package	TYPO3
 * @subpackage	ics_layar_service
 */
class tx_icslayarservice_transformation
{
	private static $tsFields = array('line2', 'line3', 'line4', 'attribution', 'actions', 'actions_label');
	private $layer;
	private $type;
	private $coords;
	private $setup;
	
	/**
	 * Initializes transformation process.
	 *
	 * @param array $layerDef Layer definition.
	 * @return void
	 */
	public function __construct(array $layerDef) {
		$this->layer = $layerDef;
		$this->type = 0;
		if (!empty($layerDef['type'])) {
			if (is_numeric($layerDef['type'])) {
				$this->type = intval($layerDef['type']);
			}
			else {
				$this->type = $layerDef['type'];
			}
		}
		$coords = $layerDef['coordinates'];
		$coords = t3lib_div::trimExplode(',', $coords, true);
		switch (count($coords)) {
			case 1:
				$this->coords = $layerDef['coordinates'];
				break;
			case 2:
				$this->coords = $coords;
				break;
		}
		foreach (self::$tsFields as $field) {
			$parser = t3lib_div::makeInstance('t3lib_TSparser');
			$parser->parse($layerDef[$field . '_ts']);
			$this->setup[$field] = $parser->setup;
		}
	}

	/**
	 * Transforms a POI record according to the layer definition.
	 *
	 * @param array $row The POI record to transform.
	 * @return array The POI structure ready to send to client.
	 */
	public function transformPOI(array $row)
	{
		$poi = array(
			'id' => null,
			'distance' => null,
			'title' => null,
			'type' => null,
			'lat' => null,
			'lon' => null,
			'actions' => array(),
		);
		$this->setRequiredFields($row, $poi);
		$generated = $this->computesTSFields($row);
		$this->setActions($row, $poi, $generated);
		$this->setOptionalFields($row, $poi, $generated);
		return $poi;
	}
	
	/**
	 * Defines the value of the required fields for the POI.
	 *
	 * @param array $row The POI record to transform.
	 * @param array &$poi The output POI definition.
	 * @return void
	 */
	protected function setRequiredFields(array $row, array &$poi) {
		$poi['id'] = md5($this->layer['source'] . $row['crdate'] . $row['uid']);
		if (!empty($this->layer['title']) && isset($row[$this->layer['title']]))
			$poi['title'] = $row[$this->layer['title']];
		if (is_string($this->type))
			$poi['type'] = intval($row[$this->type]);
		else
			$poi['type'] = $this->type;
		if (is_array($this->coords))
		{
			$poi['lat'] = round(floatval($row[$this->coords[0]]) * 1000000);
			$poi['lon'] = round(floatval($row[$this->coords[1]]) * 1000000);
		}
		else
		{
			$coords = t3lib_div::trimExplode(',', $row[$this->coords]);
			$poi['lat'] = round(floatval($coords[0]) * 1000000);
			$poi['lon'] = round(floatval($coords[1]) * 1000000);
		}
		$poi['distance'] = round(6378.137 * acos(cos(deg2rad($row['center_lat'])) * cos(deg2rad($poi['lat'])) * cos(deg2rad($row['center_lon']) - deg2rad($poi['lon'])) + sin(deg2rad($row['center_lat'])) * sin(deg2rad($poi['lat']))));
	}
	
	/**
	 * Computes the value of the layer fields defined with TypoScript.
	 *
	 * @param array $row The POI record to transform.
	 * @return void
	 */
	protected function computesTSFields(array $row) {
		$local_cObj = t3lib_div::makeInstance('tslib_cObj');
		$local_cObj->start($row, $this->layer['source']);
		$generated = array();
		foreach (self::$tsFields as $field) {
			$generated[$field] = $local_cObj->TEXT($this->setup[$field]);
		}
		return $generated;
	}
	
	/**
	 * Defines the values of the actions array for the POI.
	 *
	 * @param array $row The POI record to transform.
	 * @param array &$poi The output POI definition.
	 * @param array $generated The generated content values.
	 * @return void
	 */
	protected function setActions(array $row, array &$poi, array $generated) {
		if (empty($generated['actions'])) {
			return;
		}
		$actions = explode(',', $generated['actions']);
		$labels = explode(',', $generated['actions_label']);
		for ($i = 0; $i < count($actions) && count($poi['actions']) < 4; $i++) {
			if ($actions[$i] || $labels[$i]) {
				$poi['actions'][] = array(
					'uri' => $actions[$i],
					'label' => $labels[$i],
				);
			}
		}
	}
	
	/**
	 * Defines the value of the required fields for the POI.
	 *
	 * @param array $row The POI record to transform.
	 * @param array &$poi The output POI definition.
	 * @param array $generated The generated content values.
	 * @return void
	 */
	protected function setOptionalFields(array $row, array &$poi, array $generated) {
		foreach (array(
			'attribution',
			'line2',
			'line3',
			'line4',
		) as $field) {
			if ($generated[$field]) {
				$poi[$field] = $generated[$field];
			}
		}
		if (!empty($this->layer['image']))
		{
			if (isset($row[$this->layer['image']])) {
				$image = $row[$this->layer['image']];
				if (!empty($image)) {
					t3lib_div::loadTCA($this->layer['source']);
					if (!empty($GLOBALS['TCA'][$this->layer['source']]['columns'][$this->layer['image']]['config']['uploadfolder'])) {
						$image = $GLOBALS['TCA'][$this->layer['source']]['columns'][$this->layer['image']]['config']['uploadfolder'] . '/' . $image;
					}
				}
			}
			else {
				$image = $this->layer['image'];
			}
			if (!empty($image)) {
				$url = parse_url($image);
				if (!isset($url['scheme'])) {
					if ($url['path']{0} == '/') {
						$image = t3lib_div::getIndpEnv('TYPO3_REQUEST_HOST') . $url['path'];
					}
					else {
						$image = t3lib_div::getIndpEnv('TYPO3_SITE_URL') . $url['path'];
					}
				}
				$poi['imageURL'] = $image;
			}
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ics_layar_service/Classes/class.tx_icslayarservice_transformation.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ics_layar_service/Classes/class.tx_icslayarservice_transformation.php']);
}
