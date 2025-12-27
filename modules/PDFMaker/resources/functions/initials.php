<?php

/* A function to take a date in ($date) in specified date() format (eg mm/dd/yy for 12/08/10) and 
 * return date in $outFormat (eg d.m.Y for 20.10.1208; )
 *  datum $date - Datum containing the literal date that will be modified
 *  string $outFormat - String containing the desired date output, format the same as date()
 * 
 * [CUSTOMFUNCTION|datefmt|$INVOICE_DUEDATE$|d.m.Y|CUSTOMFUNCTION] 
 */

if (!function_exists('generateInitials')) {
    
	function generateInitials($name)
	{
		if (!$name) return '';
		$words = explode(' ', $name);
		if (!$words[0]) {
			return mb_substr($words[1], 0, 2, 'UTF-8');
		}
		if (count($words) >= 2) {
			return mb_strtoupper(
				mb_substr($words[0], 0, 1, 'UTF-8') .
					mb_substr(end($words), 0, 1, 'UTF-8'),
				'UTF-8'
			);
		}
		return makeInitialsFromSingleWord($name);
	}

	function makeInitialsFromSingleWord($name)
	{
		preg_match_all('#([A-Z]+)#', $name, $capitals);
		if (count($capitals[1]) >= 2) {
			return mb_substr(implode('', $capitals[1]), 0, 2, 'UTF-8');
		}
		return mb_strtoupper(mb_substr($name, 0, 2, 'UTF-8'), 'UTF-8');
	}

}
