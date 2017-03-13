<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Mattanja Kern <mattanja.kern@kernetics.de>
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
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 * Hint: use extdeveval to insert/update function index above.
 */

// Fix for TYPO3 >6.2 https://forge.typo3.org/issues/54144
if (!class_exists('tslib_pibase')) require_once(PATH_tslib . 'class.tslib_pibase.php');

/**
 * Plugin 'List by date' for the 'mk_listbydate' extension.
 *
 * @author	Mattanja Kern <mattanja.kern@kernetics.de>
 * @package	TYPO3
 * @subpackage	tx_mklistbydate
 */
class tx_mklistbydate_pi1 extends tslib_pibase {
	var $prefixId      = 'tx_mklistbydate_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_mklistbydate_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'mk_listbydate';	// The extension key.
	var $pi_checkCHash = true;

	var $weekdays = array(
		1 => 'Monday',
		2 => 'Tuesday',
		4 => 'Wednesday',
		8 => 'Thursday',
		16 => 'Friday',
		32 => 'Saturday',
		64 => 'Sunday'
	);

	var $binaryLastRecurrence = 16;

	/**
	 * Occurrances:
	 * 1 - first
	 * 2 - second
	 * 4 - third
	 * 8 - fourth
	 * 16 - last
	 */ 

	/**
	 * Main method of your PlugIn
	 *
	 * @param	string		$content: The content of the PlugIn
	 * @param	array		$conf: The PlugIn Configuration
	 * @return	The content that should be displayed on the website
	 */
	function main($content, $conf)	{
		$this->init();
		switch($this->lConf['displayType']) {
			case 1: // Montly
				if (strstr($this->cObj->currentRecord,'tt_content'))	{
					$conf['pidList'] = $this->cObj->data['pages'];
					$conf['recursive'] = $this->cObj->data['recursive'];
				}
				return $this->pi_wrapInBaseClass($this->monthListView($content, $conf));
			case 2: // Weekly
				if (strstr($this->cObj->currentRecord,'tt_content'))	{
					$conf['pidList'] = $this->cObj->data['pages'];
					$conf['recursive'] = $this->cObj->data['recursive'];
				}
				return $this->pi_wrapInBaseClass($this->weekListView($content, $conf));
			case 3: // Show all
				if (strstr($this->cObj->currentRecord,'tt_content'))	{
					$conf['pidList'] = $this->cObj->data['pages'];
					$conf['recursive'] = $this->cObj->data['recursive'];
				}
				return $this->pi_wrapInBaseClass($this->showAllView($content, $conf));
			default: // 0 (Simple list view)
				if (strstr($this->cObj->currentRecord,'tt_content'))	{
					$conf['pidList'] = $this->cObj->data['pages'];
					$conf['recursive'] = $this->cObj->data['recursive'];
				}
				return $this->pi_wrapInBaseClass($this->simpleView($content, $conf));
		}
	}

	function init() {
		$this->pi_initPIflexForm(); // Init and get the flexform data of the plugin
		$this->lConf = array(); // Setup our storage array...
		// Assign the flexform data to a local variable for easier access
		$piFlexForm = $this->cObj->data['pi_flexform'];
		// Traverse the entire array based on the language...
		// and assign each configuration option to $this->lConf array...
		if (is_array($piFlexForm['data'])) {
		foreach ( $piFlexForm['data'] as $sheet => $data ) {
			if (is_array($data)) {
			foreach ( $data as $lang => $value ) {
				if (is_array($value)) {
				foreach ( $value as $key => $val ) {
					$this->lConf[$key] = $this->pi_getFFvalue($piFlexForm, $key, $sheet);
				}
				}
			}
			}
		}
		}
	}

	function getWeekdayInMonthInfo($timestamp=null) {
		if ($timestamp == null) {
			// Default to current time
			$timestamp = time();
		}
		$currentWeekday = date('N',$timestamp); // 1 (for Monday) through 7 (for Sunday)
		$currentDayofmonth = date('j',$timestamp);
		$recurrence = 0;

		// Weekday of first day in this month
		$tmpWeekday = date('N', mktime(12,0,0,date('n',$timestamp),1,date('Y',$timestamp)));
		$firstOccurrence = ($currentWeekday - $tmpWeekday) + 1;
		if ($firstOccurrence < 1) $firstOccurrence += 7;

		while ($currentDayofmonth >= ($firstOccurrence + ($recurrence * 7))) {
			$recurrence++;
		}

		// Next recurrence would be greater than number of days in the given month
		$isLast = (bool)(($firstOccurrence + (($recurrence+1) * 7)) > date('t',$timestamp));

		return array('recurrence' => $recurrence,
					 'weekday' => $currentWeekday,
					 'isLast' => $isLast);
	}

	function simpleView($content, $conf) {
		$this->conf = $conf;		// Setting the TypoScript passed to this function in $this->conf
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();		// Loading the LOCAL_LANG values

		if (!isset($this->piVars['pointer']))	$this->piVars['pointer']=0;
		if (!isset($this->piVars['mode']))	$this->piVars['mode']=1;

		// Initializing the query parameters:
		list($this->internal['orderBy'],$this->internal['descFlag']) = explode(':',$this->piVars['sort']);
		$this->internal['results_at_a_time']=t3lib_utility_Math::forceIntegerInRange($lConf['results_at_a_time'],0,1000,10000);		// Number of results to show in a listing.
		$this->internal['maxPages']=t3lib_utility_Math::forceIntegerInRange($lConf['maxPages'],0,1000,2);;		// The maximum number of "pages" in the browse-box: "Page 1", "Page 2", etc.
		$this->internal['searchFieldList']='text';
		$this->internal['orderByList']='uid,text';

		/**
		 * Filter by query (instead of loop)
		 */
		$weekdayinfo = $this->getWeekdayInMonthInfo();
		$binaryWeekday = pow(2,($weekdayinfo['weekday']-1));
		$binaryRecurrence = pow(2,$weekdayinfo['recurrence']-1);
		
		// Build query
		$where = " AND (";
		// Weekday
		$where .= "(".$binaryWeekday."=(".$binaryWeekday."&weekdays)) ";
		// Week of month
		$where .= " AND (((".$binaryRecurrence."=(".$binaryRecurrence."&occurrence)))";
		if ($weekdayinfo['isLast']) {
			$where .= " OR (16=(16&occurrence))";
		}
		$where .= ")";
		// Day of month
		$where .= " OR (dayofmonth=".date('j').")";
		$where .= ")";
		// Get number of records:
		$res = $this->pi_exec_query('tx_mklistbydate_entries',1, $where);
		list($this->internal['res_count']) = $GLOBALS['TYPO3_DB']->sql_fetch_row($res);

		// Make listing query, pass query to SQL database:
		$res = $this->pi_exec_query('tx_mklistbydate_entries', 0, $where);
		$this->internal['currentTable'] = 'tx_mklistbydate_entries';

		// Put the whole list together:
		$fullTable='';	// Clear var;

		// Custom output instead of default table
		//$fullTable.=$this->pi_list_makelist($res);

		// Make list table header:
		$tRows=array();
		$this->internal['currentRow']='';
		//$tRows[] = $this->pi_list_header();

		// Make list table rows
		while($this->internal['currentRow'] = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$tRows[] = '<li>'.($this->getFieldContent('text')).'</li>';
		}

		$out = '<div'.$this->pi_classParam('listrow').'>
			<ul>
			'.implode('',$tRows).'
			</ul>
		</div>';
		return $out;
	}

	function showAllView($content, $conf) {
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();	// Loading the LOCAL_LANG values

		if (!isset($this->piVars['pointer']))	$this->piVars['pointer']=0;
		if (!isset($this->piVars['mode']))	$this->piVars['mode']=1;

		// Initializing the query parameters:
		list($this->internal['orderBy'],$this->internal['descFlag']) = explode(':',$this->piVars['sort']);
		$this->internal['results_at_a_time']=t3lib_utility_Math::forceIntegerInRange($lConf['results_at_a_time'],0,1000,10000);
		$this->internal['maxPages']=t3lib_utility_Math::forceIntegerInRange($lConf['maxPages'],0,1000,2);
		$this->internal['searchFieldList']='text';
		$this->internal['orderByList'] = 'uid,text';

		// Build query
		$where = '';
		$cat = '';
		$groupBy = 'text';
		$orderBy = 'text';

		$res = $this->pi_exec_query('tx_mklistbydate_entries',1, $where, $cat, $groupBy, $orderBy);
		list($this->internal['res_count']) = $GLOBALS['TYPO3_DB']->sql_fetch_row($res);

		// Make listing query, pass query to SQL database:
		$res = $this->pi_exec_query('tx_mklistbydate_entries', 0, $where, $cat, $groupBy, $orderBy);
		$this->internal['currentTable'] = 'tx_mklistbydate_entries';

		// Make list table header:
		$tRows=array();
		$this->internal['currentRow']='';
		//$tRows[] = $this->pi_list_header();

		// Make list table rows
		while($this->internal['currentRow'] = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$tRows[] = '<li>'.($this->getFieldContent('text')).'</li>';
		}

		$out = '<div'.$this->pi_classParam('listrow').'>
			<ul>
			'.implode('',$tRows).'
			</ul>
		</div>';
		return $out;
	}


	function monthListView($content, $conf) {
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();

		if (!isset($this->piVars['pointer']))	$this->piVars['pointer']=0;
		if (!isset($this->piVars['mode']))	$this->piVars['mode']=1;

		// Initializing the query parameters:
		list($this->internal['orderBy'],$this->internal['descFlag']) = explode(':',$this->piVars['sort']);
		$this->internal['results_at_a_time']=t3lib_utility_Math::forceIntegerInRange($lConf['results_at_a_time'],0,10000,10000); // Number of results to show in a listing.
		$this->internal['maxPages']=t3lib_utility_Math::forceIntegerInRange($lConf['maxPages'],0,1000,2); // The maximum number of "pages" in the browse-box: "Page 1", "Page 2", etc.
		$this->internal['searchFieldList']='text';
		$this->internal['orderByList']='uid,text';

		// Build query
		$where = " AND (dayofmonth>0)";
		$cat = '';
		$groupBy = '';
		$orderBy = 'dayofmonth,text';

		// Get number of records:
		$res = $this->pi_exec_query('tx_mklistbydate_entries',1, $where, $cat, $groupBy, $orderBy);
		list($this->internal['res_count']) = $GLOBALS['TYPO3_DB']->sql_fetch_row($res);

		// Make listing query, pass query to SQL database:
		$res = $this->pi_exec_query('tx_mklistbydate_entries', 0, $where, $cat, $groupBy, $orderBy);
		$this->internal['currentTable'] = 'tx_mklistbydate_entries';

		// Make list table header:
		$tRows=array();
		$this->internal['currentRow']='';

		// Make list table rows
		$cday = 0;
		while($this->internal['currentRow'] = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$tmp = $this->getFieldContent('dayofmonth');
			if ($tmp == $cday) {
				$text = '';
			} else {
				// Next day
				$cday = $tmp;
				$text = $tmp.".";
			}
			$tRows[] = '<tr><td>'.$text.'</td><td>'.$this->getFieldContent('text').'</td></tr>';
			$c++;
		}

		// table class default: '.$this->pi_classParam('listrow').'
		$out = '<div class="mklistbydate_MonthList"><table class="mklistbydate_MonthList">
			<tbody>
			'.implode('',$tRows).'
			</tbody>
		</table></div>';
		return $out;
	}

	function weekListView($content, $conf) {
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();

		if (!isset($this->piVars['pointer']))	$this->piVars['pointer']=0;
		if (!isset($this->piVars['mode']))	$this->piVars['mode']=1;

		// Initializing the query parameters:
		list($this->internal['orderBy'],$this->internal['descFlag']) = explode(':',$this->piVars['sort']);
		$this->internal['results_at_a_time']=t3lib_utility_Math::forceIntegerInRange($lConf['results_at_a_time'],0,10000,10000); // Number of results to show in a listing.
		$this->internal['maxPages']=t3lib_utility_Math::forceIntegerInRange($lConf['maxPages'],0,1000,2); // The maximum number of "pages" in the browse-box: "Page 1", "Page 2", etc.
		$this->internal['searchFieldList']='text';
		$this->internal['orderByList']='uid,text';

		// Build query
		$where = " AND weekdays<>0 AND occurrence<>0";
		$cat = '';
		$groupBy = '';
		$orderBy = 'text';

		// Get number of records:
		$res = $this->pi_exec_query('tx_mklistbydate_entries', 1, $where, $cat, $groupBy, $orderBy);
		list($this->internal['res_count']) = $GLOBALS['TYPO3_DB']->sql_fetch_row($res);

		// Make listing query, pass query to SQL database:
		$res = $this->pi_exec_query('tx_mklistbydate_entries', 0, $where, $cat, $groupBy, $orderBy);
		$this->internal['currentTable'] = 'tx_mklistbydate_entries';

		// Make list table header:
		$tRows=array();
		$this->internal['currentRow']='';

		// Make list table rows
		$data = array();
		while($this->internal['currentRow'] = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))
		{
			$text = $this->getFieldContent('text');
			$weekdays = $this->getFieldContent('weekdays');
			$occurrence = $this->getFieldContent('occurrence');

			for ($week=1; $week<=16; $week=$week*2)
			{
				if (((int)$week & $occurrence) == $week)
				{
					// Loop the weekdays
					for ($weekday = 1; $weekday<=64; $weekday=$weekday*2)
					{
						if (((int)$weekday & $weekdays) == $weekday)
						{
							$data[$week][$weekday][] = $text;
						}
					}
				}
			}
		}

		$out = '<div class="mklistbydate_WeekList"><table class="mklistbydate_WeekListView">';
		// Loop the weeks
		$llw = 0;
		for ($week=1; $week<=16; $week=$week*2)
		{
			$out .= '<tr><td class="week">'.$this->pi_getLL('tx_mklistbydate_entries.occurrence.I.'.$llw++).'</td><td><table class="mklistbydate_Days">';
			// Loop the weekdays
			$lld = 0;
			for ($weekday = 1; $weekday<=64; $weekday=$weekday*2)
			{
				$out .= '<tr><td class="day">'.$this->pi_getLL('tx_mklistbydate_entries.weekdays.I.'.$lld++).'</td><td><table class="mklistbydate_Items">';
				
				foreach ($data[$week][$weekday] as $item)
				{
					$out .= '<tr><td class="item">'.$item.'</td></tr>';
				}
				
				$out .= '</table></td></tr>';
			}
			$out .= '</table></td></tr>';
		}
		$out .= '</table></div>';
		return $out;
	}

	/**
	 * Returns the content of a given field
	 *
	 * @param	string		$fN: name of table field
	 * @return	Value of the field
	 */
	function getFieldContent($fN)	{
		switch($fN) {
			case 'uid':
				return $this->pi_list_linkSingle($this->internal['currentRow'][$fN],$this->internal['currentRow']['uid'],1);	// The "1" means that the display of single items is CACHED! Set to zero to disable caching.
			break;
			
			default:
				return $this->internal['currentRow'][$fN];
			break;
		}
	}

	/**
	 * Returns the label for a fieldname from local language array
	 *
	 * @param	[type]		$fN: ...
	 * @return	[type]		...
	 */
	function getFieldHeader($fN)	{
		switch($fN) {
			
			default:
				return $this->pi_getLL('listFieldHeader_'.$fN,'['.$fN.']');
			break;
		}
	}
	
	/**
	 * Returns a sorting link for a column header
	 *
	 * @param	string		$fN: Fieldname
	 * @return	The fieldlabel wrapped in link that contains sorting vars
	 */
	function getFieldHeader_sortLink($fN)	{
		return $this->pi_linkTP_keepPIvars($this->getFieldHeader($fN),array('sort'=>$fN.':'.($this->internal['descFlag']?0:1)));
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mk_listbydate/pi1/class.tx_mklistbydate_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mk_listbydate/pi1/class.tx_mklistbydate_pi1.php']);
}

?>