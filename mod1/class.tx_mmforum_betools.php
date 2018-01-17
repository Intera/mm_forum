<?php

/*                                                                      *
 *  COPYRIGHT NOTICE                                                    *
 *                                                                      *
 *  (c) 2010 Martin Helmich <m.helmich@mittwald.de>                     *
 *           Mittwald CM Service GmbH & Co KG                           *
 *           All rights reserved                                        *
 *                                                                      *
 *  This script is part of the TYPO3 project. The TYPO3 project is      *
 *  free software; you can redistribute it and/or modify                *
 *  it under the terms of the GNU General Public License as published   *
 *  by the Free Software Foundation; either version 2 of the License,   *
 *  or (at your option) any later version.                              *
 *                                                                      *
 *  The GNU General Public License can be found at                      *
 *  http://www.gnu.org/copyleft/gpl.html.                               *
 *                                                                      *
 *  This script is distributed in the hope that it will be useful,      *
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of      *
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the       *
 *  GNU General Public License for more details.                        *
 *                                                                      *
 *  This copyright notice MUST APPEAR in all copies of the script!      *
 *                                                                      */

use TYPO3\CMS\Core\Utility\MathUtility;


	/**
	 * 
	 * Tool collection for the mm_forum backend module. This class is intended to
	 * provide wrapper methods for TYPO3 core functions.
	 * See http://forge.typo3.org/issues/show/6919 for more information.
	 * 
	 * @author     Martin Helmich <m.helmich@mittwald.de>
	 * @package    mm_forum
	 * @subpackage Backend
	 * @copyright  2010 Martin Helmich, Mittwald CM Service GmbH & Co KG
	 * @version    $Id$
	 * 
	 */

Class tx_mmforum_BeTools {



		/**
		 *
		 * Substitutes markers in a template. Usually, this is just a wrapper method
		 * around the t3lib_parsehtml::substituteMarkerArray method. However, this
		 * method is only available from TYPO3 4.2.
		 *
		 * @param  String $template The template
		 * @param  Array  $marker   The markers that are to be replaced
		 * @return String           The template with replaced markers
		 *
		 */
	
	Function substituteMarkerArray($template, $marker) {
		
		If(TYPO3_branch === '4.1' || TYPO3_branch === '4.0')
			Return str_replace(array_keys($marker), array_values($marker), $template);
		Else Return t3lib_parsehtml::substituteMarkerArray($template, $marker);
		
	}



		/**
		 *
		 * Replaces a subpart in a template with content. This is just a wrapper method
		 * around the substituteSubpart method of the t3lib_parsehtml class.
		 *
		 * @param  String $template The tempalte
		 * @param  String $subpart  The subpart name
		 * @param  String $replace  The subpart content
		 * @return String           The template with replaced subpart.
		 *
		 */
	
	Function substituteSubpart($template, $subpart, $replace) {
		Return t3lib_parsehtml::substituteSubpart($template, $subpart, $replace);
	}



		/**
		 *
		 * Gets a subpart from a template. This is just a wrapper around the getSubpart
		 * method of the t3lib_parsehtml class.
		 *
		 * @param  String $template The template
		 * @param  String $subpart  The subpart name
		 * @return String           The subpart
		 *
		 */
	
	Function getSubpart($template, $subpart) {
		Return t3lib_parsehtml::getSubpart($template, $subpart);
	}
	
	/**
	 *
	 * Returns a mysql query in the form AND ( tablename.pid=X OR tablename.pid=Y ... )
	 * This is the same method as in tx_mmforum_tools, maybe this can be but into
	 * one place?
	 *
	 * @author Alexander Stehlik <astehlik@intera.de>
	 * @version 2011-06-15
	 * @see  typo3/sysext/cms/tslib/class.tslib_pibase.php (pi_getPidList function)
	 * @param string $pidList comma seperated list of PIDs that will be used to create the quers
	 * @param int $recursive Level of recursion when looking for child PIDs
	 * @param string $table table name, that should be prepended to the pid field
	 * @param boolean $firstOnly if true, only the first element in the userPID list will be returned (useful for new records)
	 * @param boolean $returnAsList if true, no query will be returned, but a comma seperated list of the PIDs
	 * @return string sql query string for selecting page IDs or comma sperated list of all PIDs
	 */
	static function getPIDQuery($pidList, $recursive, $table, $firstOnly = FALSE, $returnAsList = FALSE) {
		$recursive = MathUtility::forceIntegerInRange($recursive, 0);

		$userPidArray = array_unique(t3lib_div::trimExplode(',', $pidList, 1));

		if (sizeof($userPidArray) == 0) {
			die('mm_forum was not properly configured. No user storage pid was set. Please user the mm_forum Administration to set a user and group storage page.');
		} elseif (($firstOnly) || ((sizeof($userPidArray) == 1) && ($recursive == 0))) {
			$finalUserPidArray = array(MathUtility::forceIntegerInRange($userPidArray[0], 0));
		} else {

			$finalUserPidArray = array();
			$queryGenerator = t3lib_div::makeInstance('t3lib_queryGenerator');

			foreach ($userPidArray as $userPid) {

				$userPid = MathUtility::forceIntegerInRange($userPid, 0);

				if ($userPid) {
					$childPidList = $queryGenerator->getTreeList($userPid, $recursive, 0, 1);
					if ($childPidList) {
						$finalUserPidArray = array_merge($finalUserPidArray, explode(',', $childPidList));
					}
				}
			}

			$finalUserPidArray = array_unique($finalUserPidArray, SORT_NUMERIC);
		}

		if ($returnAsList) {
			$returnValue = implode(',', $finalUserPidArray);
		} else {
			$glue = ' OR ' . $table . '.pid=';
			$pidquery = implode($glue, $finalUserPidArray);
			$returnValue = ' AND ( ' . $table . '.pid=' . $pidquery . ' )';
		}

		return $returnValue;
	}
}

?>
