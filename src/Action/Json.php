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
 * The base class for any class that defines a view.
 * A view controls how templates are loaded as well as 
 * being the final point where data manipulation can occur.
 *
 * @package Eve
 */
abstract class Json extends Base  
{
    /**
     * Sets a fail format
     *
     * @param mixed
     * @param mixed
     * @return string
     */
    protected function fail($message = null, $validation = null) 
    {
        $json = array('error' => true);
        
		if($message) {
			$json['message'] = $message;
		}
		
		if($validation) {
			$json['validation'] = $validation;
		}
		
		$body = json_encode($json, JSON_PRETTY_PRINT);
		
		$this->response
			->set('headers', 'Content-Type', 'text/json')
			->set('body', $body);
		
		return $body;
    }
    
    /**
     * Sets a success format
     *
     * @param Eden\Registry\Index
     * @param mixed
     * @return string
     */
    protected function success($results = null) 
    {
        $json = array('error' => false);
        
        if($results) {
            $json['results'] = $this->body;
        }
		
		$body = json_encode($json, JSON_PRETTY_PRINT);
		
		$this->response
			->set('headers', 'Content-Type', 'text/json')
			->set('body', $body);
		
		return $body;
    }
}