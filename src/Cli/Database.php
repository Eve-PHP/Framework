<?php //-->

//activated by: generate product

namespace Eve\Framework\Cli;

class Database extends \Eve\Framework\Base
{
	protected $cwd = null;
	protected $name = null;
	protected $schema = array();
	protected $database = null;
	
	/**
	 * We need the CWD and the Schema
	 *
	 * @param string
	 */
	public function __construct($cwd) 
	{
		$this->cwd = $cwd;
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
			Index::error('Not enough arguments.', 'Usage: eve database <schema>');
		}
		
		$this->name = $args[1];
    
		$file = $this->cwd.'/schema/'.$this->name.'.php';

		if(!file_exists($file)) {
			Index::error('Cannot find schema/' . $this->name . '.php');
		}
		
		$this->schema = include($this->cwd . '/schema/' . $this->name . '.php');
		
		if(!is_array($this->schema)) {
			Index::error('Schema is invalid.');
		}
		
		$file = $this->cwd.'/settings/databases.php';
		
		if(!file_exists($file)) {
			Index::error('Cannot find settings/databases.php');
		}
		
		$databases = include($this->cwd . '/settings/databases.php');
		
		if(!is_array($databases)) {
			Index::error('Database is invalid.');
		}
		
		foreach($databases as $config) {
			if($config['type'] === 'mysql' && $config['default']) {
				$database = $config;
			}
		}
		
		if(!$database) {
			Index::error('No valid database found.', false);
			Index::error('Needs to be of mysql type and set to a default');
		}
		
		$this->database = $this(
			'mysql', 
			$database['host'], 
			$database['name'], 
			$database['user'], 
			$database['pass']);
		
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
		
		//normalize the schema
		$this->fixSchema();
		
		Index::info('Creating database table.');	
		
		$this->createTable();
		
		Index::success('`'.$this->schema['name'].'` has been successfully created.');
		
		$this->createRelations();
		
		foreach($this->schema['relations'] as $table => $many) {
			Index::success('`'.$this->schema['name'].'_'.$table.'` has been successfully created.');
		}
		
		if(!isset($this->schema['fixture'])
		|| !is_array($this->schema['fixture'])) {
			return;
		}
		
		Index::info('Fixtures found. Installing');
		
		$this->insertFixtures();
		
		Index::success('Fixtures has been successfully inserted.');
		
		die(0);
	}
	
	protected function insertFixtures()
	{
		foreach($this->schema['fixture'] as $row) {
			$model = $this->database
				->model($row)
				->save($this->schema['name']);
			
			foreach($this->schema['relations'] as $table => $many) {
				if(isset($row[$this->schema['name'].'_'.$table.'_'.$this->schema['name']])
					&& isset($row[$this->schema['name'].'_'.$table.'_'.$table])
				) {
					$model->insert($this->schema['name'].'_'.$table);
				}
			}
		}
		
		return $this;
	}
	
	protected function createRelations()
	{
		foreach($this->schema['relations'] as $table => $many) {
			$this->database->query('DROP TABLE IF EXISTS `'.$this->schema['name'].'_'.$table.'`');
			
			$this->database->query('CREATE TABLE `'.$this->schema['name'].'_'.$table.'` (
			  `'.$this->schema['name'].'_'.$table.'_'.$this->schema['name'].'` int(10) unsigned NOT NULL,
			  `'.$this->schema['name'].'_'.$table.'_'.$table.'` int(10) unsigned NOT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=utf8');	
			
			$this->database->query('ALTER TABLE `'
				. $this->schema['name'] . '_' . $table . '`'
		 		. 'ADD PRIMARY KEY (`'
				. $this->schema['name'] . '_' . $table . '_' . $this->schema['name'] . '`,`'
				. $this->schema['name'] . '_' . $table . '_' . $table . '`), '
				. 'ADD KEY `' . $table . '_id_idx` (`'
				. $this->schema['name'] . '_' . $table . '_' . $table . '`)');
		}
		
		return $this;
	}
	
	protected function createTable()
	{
		$this->database->query('DROP TABLE IF EXISTS `'.$this->schema['name'].'`');
		
		$schema = array();
		$columns = array();
		
		$schema[] = 'CREATE TABLE `'.$this->schema['name'].'` (';
		
		//primary
		$columns[] = '`'.$this->schema['name'].'_id` int(10) unsigned NOT NULL';
		
		foreach($this->schema['fields'] as $name => $field) {
			$line = array('`'.$name.'`');
			switch($field['type']) {
				case 'int':
					$line[] = 'int(10)';
					break;
				case 'float':
					$line[] = 'float(10,2)';
					break;
				case 'string':
					$line[] = 'varchar(255)';
					break;
				case 'boolean':
					$line[] = 'int(1) unsigned';
					break;
				case 'text':
				case 'datetime':
				case 'date':
				case 'time':
					$line[] = $field['type'];
					break;
			}
			
			if($field['required']) {
				$line[] = 'NOT NULL';
			}
			
			if(isset($field['default'])) {
				$line[] = 'DEFAULT '.$field['default'];
			} else if(!$field['required']) {
				$line[] = 'DEFAULT NULL';
			}
			
			$columns[] = implode(' ', $line);
		}
		
		//active
		$columns[] = "`".$this->schema['name']."_active` int(1) unsigned NOT NULL DEFAULT '1'";
		
		//type
		$columns[] = "`".$this->schema['name']."_type` varchar(255) DEFAULT NULL";
		
		//flag
		$columns[] = "`".$this->schema['name']."_flag` int(1) unsigned NOT NULL DEFAULT '0'";
		
		//created
		$columns[] = "`".$this->schema['name']."_created` datetime NOT NULL";
		
		//updated
		$columns[] = "`".$this->schema['name']."_updated` datetime NOT NULL";
		
		$schema[] = implode(",\n", $columns);
		$schema[] = ') ENGINE=InnoDB DEFAULT CHARSET=latin1;';
		
		$this->database->query(implode("\n", $schema));
		
		$this->database->query('ALTER TABLE `' . $this->schema['name'] . '` '
			. 'ADD PRIMARY KEY (`' . $this->schema['name'] . '_id`), '
			. 'ADD KEY `' . $this->schema['name'] . '_active` (`' . $this->schema['name'] . '_active`), '
			. 'ADD KEY `' . $this->schema['name'] . '_type` (`' . $this->schema['name'] . '_type`), '
			. 'ADD KEY `' . $this->schema['name'] . '_flag` (`' . $this->schema['name'] . '_flag`), '
			. 'ADD KEY `' . $this->schema['name'] . '_created` (`' . $this->schema['name'] . '_created`), '
			. 'ADD KEY `' . $this->schema['name'] . '_updated` (`' . $this->schema['name'] . '_updated`)');
		
		$this->database->query('ALTER TABLE `' . $this->schema['name'] . '` '
			. 'MODIFY `' . $this->schema['name'] . '_id` int(10) unsigned NOT NULL AUTO_INCREMENT');
		
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
			
			$valid = array();
			foreach($normal['options'] as $option) {
				$valid[] = $option['value'];
			}
			
			if($normal['type'] !== 'file') {
				$normal['valid'][] = array('one', $valid);
			}
		}
		
		return $normal;
	}
}