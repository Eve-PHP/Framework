<?php //-->

//activated by: generate product

namespace Eve\Framework\Cli;

use Handlebars\Handlebars;
use Handlebars\Loader\StringLoader as HandlebarsLoader;

class Generate extends \Eve\Framework\Base
{
	const SKIP = 'We don\'t have a template for %s/%s. Skipping..';
	
	protected $cwd = null;
	protected $name = null;
	protected $source = null;
	protected $schema = array();
	protected $namespace = null;
	protected $database = null;
	protected $engine = null;
	
	/**
	 * We need the CWD and the Schema
	 *
	 * @param string
	 */
	public function __construct($cwd) 
	{
		$this->cwd = $cwd;
		
		//we need the generator/template directory
		$this->source = __DIR__ . '/template';
		
		//make a new loader
		$loader = new HandlebarsLoader();
		//create engine
		$this->engine = new Handlebars(array('loader' => $loader));
		
		//add helpers
		$helpers = include(__DIR__.'/../Action/helpers.php');
		
		foreach($helpers as $name => $callback) {
			$this->engine->registerHelper($name, $callback);
		}
	}
	
	/**
	 * Tries to gather what we need for this task to run
	 *
	 * @param array
	 * @return this
	 */
	public function setup($args) 
	{
		if(count($args) < 2) {
			Index::error('Not enough arguments.', 'Usage: eve generate <schema> <namespace>');
		}
		
		$this->name = $args[1];
    
		if(isset($args[2])) {
			$this->namespace = $args[2];
		} else {
			$this->namespace = explode('/', $this->cwd);
			$this->namespace = array_pop($this->namespace);
			$this->namespace = ucwords($this->namespace);
			
			Index::warning('No namespace provided. Assumming it to be '.$this->namespace);
		}
		
		$file = $this->cwd.'/schema/'.$this->name.'.php';

		if(!file_exists($file)) {
			Index::error('Cannot find schema/' . $this->name . '.php');
		}
		
		$this->schema = include($this->cwd . '/schema/' . $this->name . '.php');
		
		if(!is_array($this->schema)) {
			Index::error('Schema is invalid.');
		}
		
		return $this;
	}
	
	/**
	 * Runs the CLI Generate process
	 *
	 * @return void
	 */
	public function run($args) 
	{
		$this->setup($args);
		
		Index::success('We found everything :) installing now');
		
		//normalize the schema
		$this->fixSchema();
		
		//lets get right into it
		if(isset($this->schema['model'])
			&& is_array($this->schema['model'])
		) {
			if(!is_dir($this->cwd . '/Model')) {
				mkdir($this->cwd . '/Model');
			}
			
			if(!is_dir($this->cwd . '/Model/' . ucwords($this->schema['name']))) {
				mkdir($this->cwd . '/Model/' . ucwords($this->schema['name']));
			}
			
			if(!is_dir($this->cwd . '/test/Model')) {
				mkdir($this->cwd . '/test/Model');
			}
			
			if(!is_dir($this->cwd . '/test/Model/' . ucwords($this->schema['name']))) {
				mkdir($this->cwd . '/test/Model/' . ucwords($this->schema['name']));
			}
			
			foreach($this->schema['model'] as $action) {
				$source = '/Model/' . ucwords($action) . '.html';
				
				if(!file_exists($this->source.$source)) {
					Index::error(sprintf(
						self::SKIP, 
						'Model', 
						ucwords($action)), 
					false);
					
					continue;
				}
				
				$destination = '/Model/' 
					. ucwords($this->schema['name']) . '/' 
					. ucwords($action) . '.php';
				
				$this->copy($source, $destination);
				
				//for tests
				$source = '/test/Model/' . ucwords($action) . '.html';
				
				if(!file_exists($this->source.$source)) {
					Index::error(sprintf(
						self::SKIP, 
						'test/Model', 
						ucwords($action)), 
					false);
					
					continue;
				}
				
				$destination = '/test/Model/' 
					. ucwords($this->schema['name']) . '/' 
					. ucwords($action) . '.php';
				
				$this->copy($source, $destination);
			}
		}
		
		if(isset($this->schema['page'])
			&& is_array($this->schema['page'])
		) {
			if(!is_dir($this->cwd . '/Action')) {
				mkdir($this->cwd . '/Action');
			}
			
			if(!is_dir($this->cwd . '/Action/' . ucwords($this->schema['name']))) {
				mkdir($this->cwd . '/Action/' . ucwords($this->schema['name']));
			}
		
			if(!is_dir($this->cwd . '/template')) {
				mkdir($this->cwd . '/template');
			}
			
			if(!is_dir($this->cwd . '/template/' . strtolower($this->schema['name']))) {
				mkdir($this->cwd . '/template/' . strtolower($this->schema['name']));
			}
			
			foreach($this->schema['page'] as $action) {
				$source = '/Action/' . ucwords($action) . '.html';
				
				if(!file_exists($this->source.$source)) {
					Index::error(sprintf(
						self::SKIP, 
						'Action', 
						ucwords($action)), 
					false);
					
					continue;
				}
				
				$destination = '/Action/' 
					. ucwords($this->schema['name']) . '/' 
					. ucwords($action) . '.php';
				
				$this->copy($source, $destination);
				
				if(
					!in_array(strtolower($action), 
					array('create', 'update', 'search'))
				) {
					continue;
				}
				
				$source = '/template/' . strtolower($action) . '.html';
				
				$destination = '/template/' 
					. strtolower($this->schema['name']) . '/' 
					. strtolower($action) . '.html';
				
				$this->copy($source, $destination);
			}
		}
		
		if(isset($this->schema['rest'])
			&& is_array($this->schema['rest'])
		) {
			if(!is_dir($this->cwd . '/Action/Rest')) {
				mkdir($this->cwd . '/Action/Rest');
			}
			
			if(!is_dir($this->cwd . '/Action/Rest/' . ucwords($this->schema['name']))) {
				mkdir($this->cwd . '/Action/Rest/' . ucwords($this->schema['name']));
			}
			
			foreach($this->schema['rest'] as $action) {
				$source = '/Action/Rest/' . ucwords($action) . '.html';
				
				if(!file_exists($this->source.$source)) {
					Index::error(sprintf(
						self::SKIP, 
						'Action/Rest', 
						ucwords($action)), 
					false);
					
					continue;
				}
				
				$destination = '/Action/Rest/' 
					. ucwords($this->schema['name']) . '/' 
					. ucwords($action) . '.php';
				
				$this->copy($source, $destination);
			}
		}
		
		if(isset($this->schema['job'])
			&& is_array($this->schema['job'])
		) {
			if(!is_dir($this->cwd . '/Job')) {
				mkdir($this->cwd . '/Job');
			}
			
			if(!is_dir($this->cwd . '/Job/' . ucwords($this->schema['name']))) {
				mkdir($this->cwd . '/Job/' . ucwords($this->schema['name']));
			}
			
			foreach($this->schema['job'] as $action => $instructions) {
				$source = '/Job.html';
				
				$destination = '/Job/' . ucwords($this->schema['name']) . '/' . ucwords($action) . '.php';
				
				$this->schema['job_action'] = strtolower($action);
				$this->schema['job_instructions'] = $instructions;
				
				$this->copy($source, $destination);
			}
		}
		
		Index::success($this->schema['name'].' has been successfully generated.');
		
		die(0);
	}
	
	public function copy($template, $destination) 
	{
		$contents = $this('file', $this->source . $template)->getContent();
			
		$code = $this->engine->render($contents, $this->schema);
		$code = str_replace('\\\\', '\\', $code);
		$code = str_replace('\}', '}', $code);
		$code = str_replace('\{', '{', $code);
		$code = str_replace('{ ', '{', $code);
		
		Index::info('Installing to '.$this->cwd . $destination);
		
		$this('file', $this->cwd . $destination)->setContent($code);
		
		return $this;
	}
	
	/**
	 * Fixes the schema to a common standard
	 *
	 * @return this
	 */
	protected function fixSchema() 
	{
		//what is the name?
		if(!isset($this->schema['name'])) {
			$this->schema['name'] = $this->name;
		}
		
		//what is the namespace?
		if(!isset($this->schema['namespace'])) {
			$this->schema['namespace'] = $this->namespace;
		}
		
		foreach($this->schema['fields'] as $name => $field) {
			$this->schema['fields'][$name] = $this->normalize($field);
		}
		
		return $this;
	}
	
	/**
	 * Standardizes the fields to one format
	 *
	 * @return array
	 */
	protected function normalize($field) 
	{
		$normal = array(
			'type' => 'string',
			'field' => array('text'),
			'valid' => array(),
			'label' => '',
			'holder' => '',
			'search' => false,
			'required' => false
		);
		
		if(isset($field['encoding'])) {
			$normal['encoding'] = $field['encoding'];
		}
		
		if(isset($field['type'])) {
			$normal['type'] = $field['type'];
		}
		
		if(isset($field['label'])) {
			$normal['label'] = $field['label'];
		}
		
		if(isset($field['holder'])) {
			$normal['holder'] = $field['holder'];
		}
		
		if(isset($field['search'])) {
			$normal['search'] = !!$field['search'];
		}
		
		if(isset($field['field'])) {
			if($field['field'] === false
				|| is_array($field['field'])
			) {
				$normal['field'] = $field['field'];
			} else if(is_string($field['field'])) {
				$normal['field'][0] = $field['field'];
			}
		}
		
		if(isset($field['valid'])) {
			if(is_array($field['valid'])) {
				$normal['valid'] = $field['valid'];
			} else if(is_string($field['valid'])) {
				$normal['valid'][] = array($field['valid']);
			}
				
			foreach($normal['valid'] as $i => $validation) {
				if(!is_array($validation)) {
					$validation = array($validation);
				}
				
				$normal['valid'][$i] = $validation;
				
				if($validation[0] === 'required') {
					$normal['required'] = true;
				}
			}
		}
		
		if(isset($field['default'])) {
			$normal['default'] = $field['default'];
			
			if(is_null($normal['default'])) {
				$normal['default'] = 'null';
			} else if($normal['type'] === 'int'
				&& !$this('validation', $normal['default'])->isType('int', true)
			) {
				$normal['default'] = '0';
				$normal['valid'][] = array('int');
			} else if($normal['type'] === 'float'
				&& !$this('validation', $normal['default'])->isType('float', true)
			) {
				$normal['default'] = '0.00';
				$normal['valid'][] = array('float');
			} else if($normal['type'] === 'boolean'
				&& !$this('validation', $normal['default'])->isType('bool', true)
			) {
				$normal['default'] = '0';
				$normal['valid'][] = array('bool');
			} else if($normal['type'] === 'datetime'
				&& ($normal['default'] === 'now'
				|| $normal['default'] === 'now()')
			) {
				$normal['default'] = 'CURRENT_TIMESTAMP';
				$normal['valid'][] = array('datetime');
			} else if($normal['type'] === 'date') {
				$normal['valid'][] = array('date');
			} else if($normal['type'] === 'time') {
				$normal['valid'][] = array('time');
			} else if(is_string($normal['default'])) {
				$normal['default'] = "'".$normal['default']."'";
			}
			
			$normal['default'] = (string) $normal['default'];
		}
		
		if(isset($field['options']) && is_array($field['options'])) {
			$normal['options'] = array();
			
			foreach($field['options'] as $option) {
				if(is_string($option)) {
					$normal['options'][] = array(
						'value' => $option,
						'label' => ucwords($option)
					);
					
					continue;
				}
				
				$normal['options'][] = $option;
			}
			
			if($field['field'] !== 'checkbox') {
				$valid = array();
				foreach($normal['options'] as $option) {
					$valid[] = $option['value'];
				}
				
				if($normal['type'] !== 'file') {
					$normal['valid'][] = array('one', $valid);
				}
			}
		}
		
		$validKeys = array();
		
		foreach($normal['valid'] as $check) {
			$validKeys[] = $check[0];
		}
		
		//some types should imply validation
		if(in_array($normal['type'], array(
			'bool', 
			'date',
			'float', 
			'int', 
			'email', 
			'url', 
			'small'))
			&& !in_array($normal['type'], $validKeys)
		) {
			$normal['valid'][] = array($normal['type']);
		}
		
		//datetime as well
		if($normal['type'] === 'datetime' && !in_array('date', $validKeys)) {
			$normal['valid'][] = array('date');
		}
		
		return $normal;
	}
}