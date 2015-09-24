<?php //-->
/*
 * This file is part of the Openovate Labs Inc. framework library
 * (c) 2013-2014 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE
 * distributed with this package.
 */

namespace Eve\Framework\Action;

use Handlebars\Handlebars;
use Handlebars\Loader\FilesystemLoader as HandlebarsLoader;
use Handlebars\SafeString;

/**
 * The base class for any class that defines a view.
 * A view controls how templates are loaded as well as 
 * being the final point where data manipulation can occur.
 *
 * @vendor Eve
 */
abstract class Html extends Base 
{    
    const TEMPLATE_LAYOUT = '_page';
    const TEMPLATE_EXTENSION = 'html';
    
	protected $engine = null;
	protected $layout = self::TEMPLATE_LAYOUT;
	
    protected $id = null;
    protected $title = null;
    protected $template = null;
    
    protected $meta = array();
    protected $links = array();
    protected $styles = array();
    protected $scripts = array();
    protected $messages = array();
    
    protected $head = array();
	protected $body = array();
    protected $foot = array();
    
	public function __construct() 
	{
		//get the template path
		$path = eve()->path('template');
		
		//make a new loader
		$loader = new HandlebarsLoader($path, array('extension' => self::TEMPLATE_EXTENSION));
		
		//create engine
		$this->engine = new Handlebars(array(
			'loader' => $loader,
			'partials_loader' => $loader));
		
		//add helpers
		$helpers = include(__DIR__.'/helpers.php');
		
		foreach($helpers as $name => $callback) {
			$this->engine->registerHelper($name, $callback);
		}
	}
    
    /**
     * Transform block to string
     *
     * @param Eden\Registry\Index
     * @param string
     * @return string
     */
    protected function build($template) 
    {
        if(isset($_SESSION['flash']['message'])) {
            $this->body['flash']['message'] = $_SESSION['flash']['message'];
            $this->body['flash']['type'] = $_SESSION['flash']['type'];
            unset($_SESSION['flash']);
        }
        
		$body = $this->parse($template, $this->body);
		
        $page = array(
            'meta' => $this->meta,
            'links' => $this->links,
            'styles' => $this->styles,
            'scripts' => $this->scripts,
            'title' => $this->title,
            'class' => $this->id,
            'head' => $this->head,
            'body' => $body,
            'foot' => $this->foot);
            
        $page = $this->parse($this->layout, $page);
		
		$this->response
			->set('headers', 'Content-Type', 'text/html; charset=utf-8')
			->set('body', $page);
		
		return $page;
    }
	
    /**
     * Transform block to string
     *
     * @param string|null
     * @param mixed
     * @param array
     * @return string
     */
    protected function fail(
		$message = null, 
		$errors = array(), 
		array $item = array()
	) {
		if($message) {
			$_SESSION['flash']['message'] = $message;
			$_SESSION['flash']['type'] = 'danger';
		}
		
		//if it's a string
		if(is_string($errors)) {
			//redirect will forcefully exit
			eve()->redirect($errors);
		}
		
		$this->body['errors'] = $errors;
		$this->body['item'] = $item;
		
        return $this->build($this->getTemplate());
    }
    
    /**
     * Returns file path used for templating
     *
     * @return array
     */
    protected function getTemplate() 
    {
		//if no template
        if(!$this->template) {
			//load the class
            $this->template = eve('string')
				// \Sample\Namespace\Action\Can\Be\Anywhere
				->set('\\'.get_class($this))
				// \Action\Can\Be\Anywhere
				->substr(strlen(eve()->rootNameSpace))
				// /Action/Can/Be/Anywhere
                ->str_replace('\\', DIRECTORY_SEPARATOR)
				// /Can/Be/Anywhere
				->substr(7)
                // /can/be/anywhere
				->strtolower()
				// jic
				->str_replace('//', '/')
				//done
                ->get();
        }
        
        return $this->template;
    }
    
    /**
     * Returns the template loaded with specified data
     *
     * @param string
     * @param string|null
     * @param array
     * @return string
     */
    private function parse($file, $data = array(), $trigger = null) 
    {
        if(is_null($data)) {
            $data = array();
        } else if(is_string($data)) {
            $trigger = $data;
            $data = array();
        }
		
        if($trigger) {    
            eve()->trigger('template-'.$trigger, $file, $data);
        }
        
		return $this->engine->render($file, $data);
    }
    
    /**
     * Transform block to string
     *
     * @param string|null
     * @param mixed
     * @return string
     */
    protected function success($message = null, $results = null) 
    {
		if($message) {
			$_SESSION['flash']['message'] = $message;
			$_SESSION['flash']['type'] = 'success';
		}
		
		if(is_string($results)) {
			//redirect will forcefully exit
			eve()->redirect($results);
		}
		
		if(!is_null($results)) {
			$this->body['results'] = $results;
		}
		
       	return $this->build($this->getTemplate());
    }
}