<?php //-->
/**
 * This file is part of the Eve Framework Library
 * (c) 2014-2016 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE
 * distributed with this package.
 */
namespace Eve\Framework\Cli;

/**
 * Database CLI Command
 *
 * @vendor   Eve
 * @package  Framework
 * @author   Christian Blanquera <cblanquera@openovate.com>
 * @standard PSR-2
 */
class Generate extends \Eve\Framework\Base
{
    /**
     * @const SKIP Message template
     */
    const SKIP = 'We don\'t have a template for %s/%s. Skipping..';

    /**
     * @var string|null $cwd The path from where this was called
     */
    protected $cwd = null;

    /**
     * @var string|null $name Name of the schema
     */
    protected $name = null;

    /**
     * @var string|null $source Source file
     */
    protected $source = null;

    /**
     * @var array $schema Schema data
     */
    protected $schema = array();

    /**
     * @var array $namespace Root namespace
     */
    protected $namespace = null;

    /**
     * @var array $engine Template engine
     */
    protected $engine = null;

    /**
     * We need the CWD and the Schema
     *
     * @param string $cwd The path from where this was called
     */
    public function __construct($cwd)
    {
        $this->cwd = $cwd;

        //we need the generator/template directory
        $this->source = __DIR__ . '/template';

        //create engine
        $this->engine = eden('handlebars');

        //add helpers
        $helpers = include(__DIR__.'/../Action/helpers.php');

        foreach($helpers as $name => $callback) {
            $this->engine->registerHelper($name, $callback);
        }
    }

    /**
     * Tries to gather what we need for this task to run
     *
     * @param array $args CLI arguments
     *
     * @return Eve\Framework\Cli\Generate
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


            if(file_exists($this->cwd.'/composer.json')) {
                $json = $this('file', $this->cwd.'/composer.json')->getContent();
                $json = json_decode($json, true);

                if(isset($json['autoload']['psr-4'])
                    && is_array($json['autoload']['psr-4'])
                ) {
                    foreach($json['autoload']['psr-4'] as $namespace => $path) {
                        if(strlen($path) === 0) {
                            $this->namespace = substr($namespace, 0, -1);
                            break;
                        }
                    }
                }
            }

            if(!$this->namespace) {
                $this->namespace = '';
                //$this->namespace = explode('/', $this->cwd);
                //$this->namespace = array_pop($this->namespace);
                //$this->namespace = ucwords($this->namespace);
            }

            Index::warning('No namespace provided.');
        }

        $file = $this->cwd.'/schema/'.$this->name.'.php';

        if(!file_exists($file)) {
            $file = $this->cwd.'/'.$this->name.'.php';

            if(!file_exists($file)) {
                Index::error('Cannot find /' . $this->name . '.php');
            }
        }

        $this->schema = include($file);

        if(!is_array($this->schema)) {
            Index::error('Schema is invalid.');
        }

        return $this;
    }

    /**
     * Runs the CLI Generate process
     *
     * @param array $args CLI arguments
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
            $this->generateModels();
            $this->generateModelTests();
        }

        if(isset($this->schema['job'])
            && is_array($this->schema['job'])
        ) {
            $this->generateJobs();
            $this->generateJobTests();
        }

        if(isset($this->schema['page'])
            && is_array($this->schema['page'])
        ) {
            $this->generatePages();
            $this->generatePageTests();
            $this->generateTemplates();
        }

        if(isset($this->schema['rest'])
            && is_array($this->schema['rest'])
        ) {
            $this->generateRests();
            $this->generateRestTests();
        }

        Index::success($this->schema['name'].' has been successfully generated.');

        die(0);
    }

    /**
     * Generate models
     *
     * @return Eve\Framework\Cli\Generate
     */
    public function generateModels()
    {
        $sourceRoot = $this->source . '/Model';

        $destinationRoot = $this->cwd
            . $this->schema['paths']['model']
            . '/' . ucwords($this->schema['name']);

        if(!is_dir($destinationRoot)) {
            mkdir($destinationRoot, 0777, true);
        }

        foreach($this->schema['model'] as $action) {
            $source = $sourceRoot . '/' . ucwords($action) . '.html';

            $destination = $destinationRoot . '/' . ucwords($action) . '.php';

            if(!file_exists($source)) {
                Index::error(sprintf(
                    self::SKIP,
                    'Model',
                    ucwords($action)),
                false);

                continue;
            }

            $this->copy($source, $destination);
        }
    }

    /**
     * Generate model tests
     *
     * @return Eve\Framework\Cli\Generate
     */
    public function generateModelTests()
    {
        $sourceRoot = $this->source . '/test/Model';

        $destinationRoot = $this->cwd
            . '/test'
            . $this->schema['paths']['model']
            . '/' . ucwords($this->schema['name']);

        if(!is_dir($destinationRoot)) {
            mkdir($destinationRoot, 0777, true);
        }

        foreach($this->schema['model'] as $action) {
            $source = $sourceRoot . '/' . ucwords($action) . '.html';
            $destination = $destinationRoot . '/' . ucwords($action) . '.php';

            if(!file_exists($source)) {
                Index::error(sprintf(
                    self::SKIP,
                    'test/Model',
                    ucwords($action)),
                false);

                continue;
            }

            $this->copy($source, $destination);
        }
    }

    /**
     * Generate jobs
     *
     * @return Eve\Framework\Cli\Generate
     */
    public function generateJobs()
    {
        $destinationRoot = $this->cwd
            . $this->schema['paths']['job']
            . '/' . ucwords($this->schema['name']);

        if(!is_dir($destinationRoot)) {
            mkdir($destinationRoot, 0777, true);
        }

        foreach($this->schema['job'] as $action => $instructions) {
            $source = $this->source . '/Job.html';

            $destination = $destinationRoot . '/' . ucwords($action) . '.php';

            $this->schema['job_action'] = strtolower($action);
            $this->schema['job_instructions'] = $instructions;

            $this->copy($source, $destination);
        }
    }

    /**
     * Generate job tests
     *
     * @return Eve\Framework\Cli\Generate
     */
    public function generateJobTests()
    {
        $sourceRoot = $this->source . '/test/Job';

        $destinationRoot = $this->cwd
            . '/test'
            . $this->schema['paths']['job']
            . '/' . ucwords($this->schema['name']);

        if(!is_dir($destinationRoot)) {
            mkdir($destinationRoot, 0777, true);
        }

        foreach($this->schema['job'] as $action => $instructions) {
            $source = $sourceRoot . '/' . ucwords($action) . '.html';
            $destination = $destinationRoot . '/' . ucwords($action) . '.php';

            if(!file_exists($source)) {
                Index::error(sprintf(
                    self::SKIP,
                    'test/Job',
                    ucwords($action)),
                false);

                continue;
            }

            $this->copy($source, $destination);
        }
    }

    /**
     * Generate page actions
     *
     * @return Eve\Framework\Cli\Generate
     */
    public function generatePages()
    {
        $sourceRoot = $this->source . '/Action';

        $destinationRoot = $this->cwd
            . $this->schema['paths']['page']
            . '/' . ucwords($this->schema['name']);

        if(!is_dir($destinationRoot)) {
            mkdir($destinationRoot, 0777, true);
        }

        foreach($this->schema['page'] as $action) {
            $source = $sourceRoot . '/' . ucwords($action) . '.html';

            $destination = $destinationRoot . '/' . ucwords($action) . '.php';

            if(!file_exists($source)) {
                Index::error(sprintf(
                    self::SKIP,
                    'Action',
                    ucwords($action)),
                false);

                continue;
            }

            $this->copy($source, $destination);
        }
    }

    /**
     * Generate page tests
     *
     * @return Eve\Framework\Cli\Generate
     */
    public function generatePageTests()
    {
        $sourceRoot = $this->source . '/test/Action';

        $destinationRoot = $this->cwd
            . '/test'
            . $this->schema['paths']['page']
            . '/' . ucwords($this->schema['name']);

        if(!is_dir($destinationRoot)) {
            mkdir($destinationRoot, 0777, true);
        }

        foreach($this->schema['page'] as $action) {
            $source = $sourceRoot . '/' . ucwords($action) . '.html';
            $destination = $destinationRoot . '/' . ucwords($action) . '.php';

            if(!file_exists($source)) {
                Index::error(sprintf(
                    self::SKIP,
                    'test/Action',
                    ucwords($action)),
                false);

                continue;
            }

            $this->copy($source, $destination);
        }
    }

    /**
     * Generate page templates
     *
     * @return Eve\Framework\Cli\Generate
     */
    public function generateTemplates()
    {
        $sourceRoot = $this->source . '/template';

        $destinationRoot = $this->cwd
            . $this->schema['paths']['template']
            . '/' . strtolower($this->schema['name']);

        if(!is_dir($destinationRoot)) {
            mkdir($destinationRoot, 0777, true);
        }

        foreach($this->schema['page'] as $action) {
            $source = $sourceRoot . '/' . strtolower($action) . '.html';

            $destination = $destinationRoot . '/' . strtolower($action) . '.html';

            if(!file_exists($source)) {
                Index::error(sprintf(
                    self::SKIP,
                    'template',
                    strtolower($action)),
                false);

                continue;
            }

            $this->copy($source, $destination);
        }
    }

    /**
     * Generate REST actions
     *
     * @return Eve\Framework\Cli\Generate
     */
    public function generateRests()
    {
        $sourceRoot = $this->source . '/Action/Rest';

        $destinationRoot = $this->cwd
            . $this->schema['paths']['rest']
            . '/' . ucwords($this->schema['name']);

        if(!is_dir($destinationRoot)) {
            mkdir($destinationRoot, 0777, true);
        }

        foreach($this->schema['rest'] as $action) {
            $source = $sourceRoot . '/' . ucwords($action) . '.html';

            $destination = $destinationRoot . '/' . ucwords($action) . '.php';

            if(!file_exists($source)) {
                Index::error(sprintf(
                    self::SKIP,
                    'Action',
                    ucwords($action)),
                false);

                continue;
            }

            $this->copy($source, $destination);
        }
    }

    /**
     * Generates REST tests
     *
     * @return Eve\Framework\Cli\Generate
     */
    public function generateRestTests()
    {
        $sourceRoot = $this->source . '/test/Action/Rest';

        $destinationRoot = $this->cwd
            . '/test'
            . $this->schema['paths']['rest']
            . '/' . ucwords($this->schema['name']);

        if(!is_dir($destinationRoot)) {
            mkdir($destinationRoot, 0777, true);
        }

        foreach($this->schema['rest'] as $action) {
            $source = $sourceRoot . '/' . ucwords($action) . '.html';
            $destination = $destinationRoot . '/' . ucwords($action) . '.php';

            if(!file_exists($source)) {
                Index::error(sprintf(
                    self::SKIP,
                    'test/Action/Rest',
                    ucwords($action)),
                false);

                continue;
            }

            $this->copy($source, $destination);
        }
    }

    /**
     * Copy the contents from to
     *
     * @return Eve\Framework\Cli\Generate
     */
    public function copy($source, $destination)
    {
        $contents = $this('file', $source)->getContent();

        $template = $this->engine->compile($contents);
        $code = $template($this->schema);
        $code = str_replace('\\\\', '\\', $code);
        $code = str_replace('\}', '}', $code);
        $code = str_replace('\{', '{', $code);
        $code = str_replace('{ ', '{', $code);

        Index::info('Installing to' . $destination);

        $this('file', $destination)->setContent($code);

        return $this;
    }

    /**
     * Fixes the schema to a common standard
     *
     * @return Eve\Framework\Cli\Generate
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

        if(!isset($this->schema['url'])) {
            $this->schema['url'] = '';
        }

        //paths
        $paths = array(
            'rest'         => '/Action/Rest',
            'page'         => '/Action',
            'model'     => '/Model',
            'job'         => '/Job',
            'template'     => '/template'
        );

        foreach($paths as $key => $path) {
            if(!isset($this->schema['paths'][$key])) {
                $this->schema['paths'][$key] = $path;
            }

            $this->schema[$key.'_namespace'] = trim($this->namespace
                . str_replace('/', '\\', $this->schema['paths'][$key]), '\\');

            $this->schema[$key.'_test_namespace'] = trim($this->namespace
                . str_replace(' ', '', ucwords(str_replace('/', ' ', $this->schema['paths'][$key]))), '\\');
        }

        foreach($this->schema['fields'] as $name => $field) {
            $this->schema['fields'][$name] = $this->normalize($field);
        }

        return $this;
    }

    /**
     * Standardizes the fields to one format
     *
     * @param array $field The field schema to normalize
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

        if(isset($field['sample'])) {
            $normal['sample'] = $field['sample'];
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

        if(!isset($normal['sample']) && isset($normal['default'])) {
            $normal['sample'] = $normal['default'];
        }

        if(!isset($normal['sample'])) {
            $sample = 'foobar';
            foreach($normal['valid'] as $valid) {
                switch($valid[0]) {
                    case 'required':
                    case 'empty':
                        break;
                    case 'one':
                        $sample = $valid[1][0];
                        break;
                    case 'email':
                        $sample = 'test@test.com';
                        break;
                    case 'hex':
                        $sample = '12321';
                        break;
                    case 'cc':
                        $sample = '4111111111111111';
                        break;
                    case 'html':
                        $sample = '<p>Awesome</p>';
                        break;
                    case 'url':
                        $sample = 'http://example.com';
                        break;
                    case 'slug':
                        $sample = 'asd-123';
                        break;
                    case 'json':
                        $sample = '{"error":false}';
                        break;
                    case 'date':
                        $sample = '2015-09-02';
                        break;
                    case 'time':
                        $sample = '12:01:00';
                        break;
                    case 'alphanum':
                        $sample = 'foo123';
                        break;
                    case 'alphanum-':
                        $sample = 'foo-123';
                        break;
                    case 'alphanum_':
                        $sample = 'foo_123';
                        break;
                    case 'alphanum-_':
                        $sample = 'foo-_123';
                        break;
                    case 'bool':
                        $sample = '1';
                        break;
                    case 'small':
                        $sample = '3';
                        break;
                    case 'int':
                        $sample = '3';
                        break;
                    case 'float':
                        $sample = '3.3';
                        break;
                    case 'price':
                        $sample = '3.30';
                        break;
                }
            }

            $normal['sample'] = "'".$sample."'";
        }

        return $normal;
    }
}