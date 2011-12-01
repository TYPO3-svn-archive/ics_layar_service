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
 * Represents the Layar service content transformer from database to POI.
 *
 * @author	Pierrick Caillon <pierrick@in-cite.net>
 * @package	TYPO3
 * @subpackage	ics_layar_service
 */
class tx_icslayarservice_transformation
{
	private $layer;
	private $type;
	private $coords;
	private $setup;
	
	/**
	 * Initializes transformation process.
	 *
	 * @param array $row: Layer definition.
	 * @return void
	 */
	public function init(array $layerDef) {
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
		// TODO: Initialize TS fields.
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
			'actions' => array(),
			'attribution' => null,
			'distance' => null,
			'id' => null,
			'imageURL' => null,
			'lat' => null,
			'lon' => null,
			'line2' => null,
			'line3' => null,
			'line4' => null,
			'title' => null,
			'type' => null,
		);
		$poi['id'] = md5($this->layer['table'] . $row['crdate'] . $row['uid']);
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
		if (!empty($this->layer['image']))
		{
			if (preg_match('/^[A-Z0-9_-]+$/i', $this->layer['image']))
				$image = $row[$this->layer['image']];
			else
				$image = $this->layer['image'];
			// TODO: parse image url
		}
		// TODO: The other fields
		$poi['distance'] = round(6378.137 * acos(cos(deg2rad($row['center_lat'])) * cos(deg2rad($poi['lat'])) * cos(deg2rad($row['center_lon']) - deg2rad($poi['lon'])) + sin(deg2rad($row['center_lat'])) * sin(deg2rad($poi['lat']))));
		return $poi;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ics_layar_service/Classes/class.tx_icslayarservice_transformation.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ics_layar_service/Classes/class.tx_icslayarservice_transformation.php']);
}
