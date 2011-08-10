<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 In Cité Solution <technique@in-cite.net>
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

define('LAYAR_POI_NEEDPAGE_COUNT', 15);	// Above this count, paging is required.
define('LAYAR_POI_BYPAGE', 10);	// Number of POI per page if paging.
define('LAYAR_SESSION_NAME', 'T3_layar_pager');

/**
 * Represents the Layar service content page manager.
 *
 * @author	Pierrick Caillon <pierrick@in-cite.net>
 * @package	TYPO3
 * @subpackage	ics_layar_service
 */
class tx_icslayarservice_pager
{
	var $pois;
	var $pageKey;
	
	function start($pois)
	{
		$this->pois = $pois;
		if (count($pois) > LAYAR_POI_NEEDPAGE_COUNT)
		{
			$oldsessionid = session_id($this->pageKey = uniqid(''));
			$oldsessionname = session_name(LAYAR_SESSION_NAME);
			$pages = array(array());
			$currentPage = 0;
			while (!empty($pois))
			{
				if (count($pages[$currentPage]) == LAYAR_POI_BYPAGE)
					$pages[++$currentPage] = array();
				$pages[$currentPage][] = array_shift($pois);
			}
			if (session_start())
			{
				$this->pois = array_shift($pages);
				$_SESSION['pages'] = $pages;
				session_write_close();
			}
			else
				$this->pageKey = null;
			session_id($oldsessionid);
			session_name($oldsessionname);
		}
	}
	
	function load($pageKey)
	{
		$result = true;
		$oldsessionid = session_id($pageKey);
		$oldsessionname = session_name(LAYAR_SESSION_NAME);
		if (session_start())
		{
			$pages = $_SESSION['pages'];
			session_destroy();
		}
		else
		{
			$pages = array();
			$result = false;
		}
		if (!empty($pages))
		{
			$this->pois = array_shift($pages);
			if (!empty($pages))
			{
				session_id($this->pageKey = uniqid(''));
				if (session_start())
				{
					$_SESSION['pages'] = $pages;
					session_write_close();
				}
				else
				{
					$this->pois = array();
					$this->pageKey = null;
					$result = false;
				}
			}
		}
		session_id($oldsessionid);
		session_name($oldsessionname);
		return $result;
	}
	
	function getPage()
	{
		return $this->pois;
	}
	
	function getKey()
	{
		return $this->pageKey;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ics_layar_service/class.tx_icslayarservice_pager.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ics_layar_service/class.tx_icslayarservice_pager.php']);
}
