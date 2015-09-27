<?php //-->
/*
 * This file is part of the Eve Framework Library
 * (c) 2014-2016 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE
 * distributed with this package.
 */

namespace Eve\Framework\Model;

/**
 * Exception
 *
 * @package Eve
 */
class Base extends \Eve\Framework\Base 
{
	
	const FAIL_400 = 'Invalid Parameters';
	
	//basic
	const INVALID_ID = 'Invalid ID';
	const INVALID_REQUIRED = 'Cannot be empty';
	const INVALID_EMPTY = 'Cannot be empty, if set';
    const INVALID_ONEOF = 'Must be one of %s';
	
	//patterns
	const INVALID_EMAIL = 'Should be a valid email';
	const INVALID_HEX = 'Should be valid hexidecimal';
	const INVALID_CC = 'Should be a valid credit card';
	const INVALID_HTML = 'Should be valid HTML';
	const INVALID_URL = 'Should be a valid url';
	const INVALID_SLUG = 'Should be only alpha-numeric optional hyphens';
	const INVALID_JSON = 'Should be valid JSON';
	const INVALID_DATE = 'Should be a valid date (YYYY-MM-DD)';
	const INVALID_TIME = 'Should be a valid time (HH:MM:SS)';
	const INVALID_REGEX = 'Invalid Format';
	
	//alpha
	const INVALID_ALPHANUM = 'Should be only alpha-numeric';
	const INVALID_ALPHANUM_HYPHEN = 'Should be only alpha-numeric optional hyphens';
	const INVALID_ALPHANUM_SCORE = 'Should be only alpha-numeric optional underscore';
	const INVALID_ALPHANUM_LINE = 'Should be only alpha-numeric optional hyphens or underscore';
	
	//numbers
	const INVALID_BOOL = 'Should either be 0 or 1';
	const INVALID_SMALL = 'Should be between 0 and 9';
	const INVALID_INT = 'Should be a valid integer';
	const INVALID_FLOAT = 'Should be a valid floating point';
	const INVALID_NUMBER = 'Should be a valid number';
	const INVALID_PRICE = 'Should be a valid price';
	
	
	/**
	 * make everything into a string
	 * remove empty strings
	 *
	 * @param object
	 * @return object
	 */
	public function prepare($item)
	{
		$prepared = array();
		
		foreach($item as $key => $value) {
			//if it's null
			if($value === null) {
				//set it and continue
				$prepared[$key] = null;
				continue;
			}
			
			//if is array
			if(is_array($value)) {
				//recursive
				$prepared[$key] = $this->prepare($value);
				continue;
			}
			
			//if it can be converted
			if(is_scalar($value)) {
				$prepared[$key] = (string) $value;
				continue;
			}
			
			//we tried our best ...
			$prepared[$key] = $value;
		}
		
		return $prepared;
	}
}