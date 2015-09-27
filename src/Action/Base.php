<?php //-->
/*
 * This file is part of the Eve Framework Library
 * (c) 2014-2016 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE
 * distributed with this package.
 */

namespace Eve\Framework\Action;

/**
 * ACtion Base
 *
 * @vendor Eve
 */
abstract class Base extends \Eve\Framework\Base 
{
	protected $request = null;
	protected $response = null;
	
	/**
     * Get request
     *
     * @return Eden\Registry\Index
     */
    public function getRequest() 
	{
		return $this->request;
	}
	
	/**
     * Get response
     *
     * @return Eden\Registry\Index
     */
    public function getReponset() 
	{
		return $this->response;
	}
	
	/**
     * Transform block to string
     *
     * @return string
     */
    abstract public function render();
	
	/**
     * Set request
     *
     * @param Eden\Registry\Index
     * @return this
     */
    public function setRequest($request) 
	{
		$this->request = $request;
		return $this;
	}
	
	/**
     * Set response
     *
     * @param Eden\Registry\Index
     * @return this
     */
    public function setResponse($response) 
	{
		$this->response = $response;
		return $this;
	}
}