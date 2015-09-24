<?php //-->
/*
 * This file is part of the Eden package.
 * (c) 2014-2016 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
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