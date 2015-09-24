<?php //-->
/*
 * This file is part of the Eden package.
 * (c) 2014-2016 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Eve\Framework\Job;

/**
 * Exception
 *
 * @vendor Eve
 */
abstract class Base extends \Eve\Framework\Base 
{
	protected $data = null;
	
	/**
	 * Executes the job
	 *
	 * @return void
	 */
	abstract public function run();
	
	/**
	 * Sets data needed for the job
	 *
	 * @param mixed data
	 * @return this
	 */
	public function setData($data) 
	{
		$this->data = $data;
		return $this;
	}
}