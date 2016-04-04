<?php //-->
/**
 * This file is part of the Eve Framework Library
 * (c) 2014-2016 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE
 * distributed with this package.
 */
namespace
{
    if(!function_exists('eve')) {
        /**
         * The starting point of every application call. If you are only
         * using the framework you can rename this function to whatever you
         * like.
         */
        function eve()
        {
            $class = Eve\Framework\Index::i();
    
            if(func_num_args() == 0) {
                return $class;
            }
    
            $args = func_get_args();
    
            return $class->__invoke($args);
        }
    }
}

namespace Eve\Framework
{
    /**
     * Defines the starting point of every site call.
     * Starts laying out how classes and methods are handled.
     *
     * @vendor   Eve
     * @package  Framework
     * @author   Christian Blanquera <cblanquera@openovate.com>
     * @standard PSR-2
     */
    class Index extends \Eden\Server\Index
    {
        /**
         * @const int INSTANCE multiple or singleton
         */
        const INSTANCE = 1;
        
        /**
         * @const string NO_JOB error template
         */
        const NO_JOB = 'No Job: %s Found';
        
        /**
         * @const string NO_MODEL error template
         */
        const NO_MODEL = 'No Model: %s Found';
        
        /**
         * @const string NO_BLOCK error template
         */
        const NO_BLOCK = 'No Block: %s Found';

        /**
         * @var string|null $rootUrl
         */
        public $rootUrl = null;

        /**
         * @var string|null $rootPath
         */
        public $rootPath = null;

        /**
         * @var string|null $defaultDatabase
         */
        public $defaultDatabase = null;

        /**
         * @var string|null $defaultRegistry
         */
        public $defaultRegistry = null;

        /**
         * @var string|null $defaultLanguage
         */
        public $defaultLanguage = null;

        /**
         * @var string|null $defaultQueue
         */
        public $defaultQueue = null;

        /**
         * @var string|null $rootNameSpace
         */
        protected $rootNameSpace = null;

        /**
         * @var string|null $routeNameSpace
         */
        protected $routeNameSpace = null;

        /**
         * @var array $cachedSettings
         */
        protected $cachedSettings = array();

        /**
         * Set the root and namespace
         *
         * @param string|null $rootPath  The root path
         * @param string|null $namespace The Root namespace
         * @param string|null $rootUrl   The root url (for sites not in the root)
         *
         * @return void
         */
        public function __construct(
            $rootPath = null,
            $namespace = null,
            $rootUrl = null
        ) {
            Argument::i()
                ->test(1, 'string', 'null')
                ->test(2, 'string', 'null')
                ->test(3, 'string', 'null');

            if($rootPath) {
                $this->setRootPath($rootPath);
            }

            if($rootUrl) {
                $this->setRootUrl($rootUrl);
            }

            if($namespace) {
                $this->setRootNamespace($namespace);
            }
        }

        /**
         * Loads an HTML block
         *
         * @param *string $key The block name
         *
         * @return mixed
         */
        public function block($key)
        {
            Argument::i()->test(1, 'string');
            
            $key = str_replace(array('_', '/'), ' ', $key);
            $key = ucwords($key);
            $key = str_replace(' ', '\\', $key);
            $class = $key;
            
            if(!class_exists($class)) {
                $class = $this->rootNameSpace.'\\Block\\' . $key;
    
                if(!class_exists($class)) {
                    $path = $this->rootPath 
                        . '/Block/' 
                        . str_replace('\\', '/', trim($key, '\\')) 
                        . '.php';
                    
                    if(!file_exists($path)) {
                        $path = $this->rootPath 
                            . '/vendor/' 
                            . str_replace('\\', '/', trim($key, '\\')) 
                            . '.php';
                        
                        if(!file_exists($path)) {
                            throw new Exception(sprintf(self::NO_BLOCK, $key));    
                        }
                    }
                    
                    return include($path);
                }
            }
            
            if(strpos($class, '\\') === 0) {
                //remove starting \\
                $class = substr($class, 1);
            }            
            
            return $this->$class();
        }

        /**
         * Runs the default bootstrap from start to finish
         * If you wish to add process between these steps
         * you should copy the method details and paste to
         * index.php
         *
         * @return Eve\Framework\Index
         */
        public function defaultBootstrap()
        {
            return $this
                ->defaultPaths()
                ->defaultDebugging()
                ->defaultErrorHandler()
                ->defaultDatabases()
                ->trigger('config')
                ->defaultTimezone('Asia/Manila')
                ->trigger('init')
                ->defaultSession()
                ->trigger('session')
                ->defaultRouting()
                ->trigger('request')
                ->defaultResponse()
                ->trigger('response')
                ->render()
                ->trigger('render')
                ->trigger('shutdown');
        }

        /**
         * Sets up the default database connection
         *
         * @param array|null $databases Inject a database config
         *
         * @return Eve\Framework\Index
         */
        public function defaultDatabases(array $databases = null)
        {
            if(!$databases 
                && !empty($_SERVER) 
                && isset($_SERVER['HTTP_HOST'])
                && strpos($_SERVER['HTTP_HOST'], 'testsuites') !== false
            ) {
                $test = $this->settings('test');
                $databases = $test['database'];
            }

            if(!$databases) {
                $databases = $this->settings('databases');
            }

            foreach($databases as $key => $info) {
                //connect to the data as described in the settings
                switch($info['type']) {
                    case 'postgre':
                        $database = $this(
                            'postgre',
                            $info['host'],
                            $info['name'],
                            $info['user'],
                            $info['pass']);
                        break;
                    case 'mysql':
                        $database = $this(
                            'mysql',
                            $info['host'],
                            $info['name'],
                            $info['user'],
                            $info['pass']);
                        break;
                    case 'sqlite':
                        $database = $this('sqlite', $info['file']);
                        break;
                }

                // Allow custom objects
                if (is_object($info['type'])) {
                    $database = $info['type'];
                }
                
                $this->registry()->set('database', $key, $database);

                if($info['default']) {
                    $this->defaultDatabase = $database;
                }
            }

            return $this;
        }

        /**
         * Lets the framework handle exceptions.
         * This is useful in the case that you
         * use this framework on a server with
         * no xdebug installed.
         *
         * @return Eve\Framework\Index
         */
        public function defaultDebugging()
        {
            //get settings from config
            $config = $this->settings('config');

            //save it for later
            $this->registry()->set('config', $config);

            if(!isset($config['debug_mode'])) {
                $config['debug_mode'] = E_ALL;
            }

            //if debug mode is on
            if(!$config['debug_mode']) {
                //stop argument testing
                Argument::i()->stop();
            } else if (!ini_get('display_errors')) {
                ini_set('display_errors', '1');
            }

            //turn on error handling
            $error = $this('handler')
                ->error()
                ->register()
                ->setReporting($config['debug_mode']);

            //turn on exception handling
            $exception = $this('handler')
                ->exception()
                ->register();

            return $this;
        }

        /**
         * Sets Default Error Handlers
         *
         * @return Eve\Framework\Index
         */
        public function defaultErrorHandler()
        {
            //this happens on an error
            $this->error(function($request, $response) {
                $args = func_get_args();
                $request = array_shift($args);
                $response = array_shift($args);

                $mode = $this->registry()->get('config', 'debug_mode');

                $type = 'text/plain';
                if(!$response->isKey('headers', 'Content-Type')) {
                    $response->set('headers', 'Content-Type', $type);
                } else {
                    $type = $response->get('headers', 'Content-Type');
                }

                $handler = new Error();

                switch(true) {
                    case strpos($type, 'html') !== false && $mode:
                        $body = $handler->callArray('htmlDetails', $args);
                        break;
                    case strpos($type, 'html') !== false:
                        $body = $handler->callArray('htmlGeneric', $args);
                        break;
                    case strpos($type, 'json') !== false && $mode:
                        $body = $handler->callArray('jsonDetails', $args);
                        break;
                    case strpos($type, 'json') !== false:
                        $body = $handler->callArray('jsonGeneric', $args);
                        break;
                    case $mode:
                        $body = $handler->callArray('plainDetails', $args);
                        break;
                    default:
                        $body = $handler->callArray('plainGeneric', $args);
                        break;
                }

                $response->set('body', $body);
            });

            return $this;
        }

        /**
         * Sets the application absolute paths
         * for later referencing
         *
         * @return Eve\Framework\Index
         */
        public function defaultPaths()
        {
            $root = $this->rootPath;

            if(!$this->registry()->isKey('path', 'root')) {
                $this->registry()->set('path', 'root', $root);
            }

            $root = $this->registry()->get('path', 'root');

            $paths = array(
                'settings',
                'upload',
                'vendor',

                //PHP folders
                'Action',
                'Event',
                'Job',
                'Model',

                //Other Folders
                'template',
                'public');

            foreach($paths as $path) {
                if(!$this->registry()->isKey('path', strtolower($path))) {
                    $this->registry()->set('path', strtolower($path), $root . '/' . $path);
                }
            }

            return $this;
        }

        /**
         * Sets response
         *
         * @param string|null the request object
         * @return Eve\Framework\Index
         */
        public function defaultResponse()
        {
            $this->all('**', function($request, $response) {
                //if there is already a body or not an action
                if($response->isKey('body') || !$response->isKey('action')) {
                    //do nothing
                    return;
                }

                $action = $response->get('action');

                if(is_callable($action)) {
                    $action = $action->bindTo($this, get_class($this));
                    //call it
                    $results = $action($request, $response);
                    //if there are results
                    //and no body was set
                    if($results
                    && is_scalar($results)
                    && !$response->isKey('body')) {
                        $response->set('body', (string) $results);
                    }

                    return;
                }

                //it's a class
                $instance = new $action();

                //call it
                $results = $instance
                    ->setRequest($request)
                    ->setResponse($response)
                    ->render();

                //if there are results
                //and no body was set
                if($results
                && is_scalar($results)
                && !$response->isKey('body')) {
                    $response->set('body', (string) $results);
                }
            });

            return $this;
        }

        /**
         * Sets Dynamic routes base on the request
         *
         * @return Eve\Framework\Index
         */
        public function defaultRouting($routeNameSpace = null)
        {
            //just call the parent
            $this->all('**', function($request, $response) use ($routeNameSpace) {
                //if there is already a body or action
                if($response->isKey('body') || $request->isKey('action')) {
                    //do nothing
                    return;
                }

                //determine the route namespace
                $routeNameSpace = $this->routeNameSpace;

                if(!$routeNameSpace) {
                    $routeNameSpace = $this->rootNameSpace;
                }

                $prefix = $routeNameSpace . '\\Action';
                $root = $this->registry()->get('path', 'action');

                $path = $request['path']['string'];
                $path = substr($path, strlen($this->rootUrl));
                $array = explode('/', $path);

                $variables = array();
                $action = null;
                $buffer = $array;

                while(count($buffer) > 1) {
                    $parts = ucwords(implode(' ', $buffer));

                    //try to see if it's callable
                    $file = $root.str_replace(' ', '/', $parts).'.php';

                    if(file_exists($file)) {
                        $contents = include($file);
                        if(is_callable($contents)) {
                            $action = $contents;
                            break;
                        }
                    }

                    //try to see if it's a class
                    $class = $prefix.str_replace(' ', '\\', $parts);

                    if(class_exists($class)) {
                        $action = $class;
                        break;
                    }

                    $variable = array_pop($buffer);
                    array_unshift($variables, $variable);
                }

                if(!$action || !class_exists($action)) {
                    $defaultAction = $this->registry()->get('config', 'default_action');

                    if(!$defaultAction) {
                        $defaultAction = 'index';
                    }

                    $defaultAction = ucwords($defaultAction);

                    //try to see if it's callable
                    $file = $root.'/'.$defaultAction.'.php';

                    if(file_exists($file)) {
                        $contents = include($file);
                        if(is_callable($contents)) {
                            $action = $contents;
                        }
                    }

                    //try to see if it's a class
                    $default = $prefix.'\\'.$defaultAction;

                    if(class_exists($default)) {
                        $action = $default;
                    }
                }

                //set the variables if it has not been set
                if(!$request->isKey('variables')) {
                    $request->set('variables', $variables);
                }

                //if we have an action
                if($action) {
                    //set the action
                    $response->set('action', $action);
                }
            });

            return $this;
        }

        /**
         * Starts a session
         *
         * @return Eve\Framework\Index
         */
        public function defaultSession()
        {
            session_start();

            return $this;
        }

        /**
         * Sets the PHP timezone
         *
         * @param *string $zone The timezone identifier
         *
         * @return Eve\Framework\Index
         */
        public function defaultTimezone($zone = 'GMT')
        {
            $settings = $this->settings('config');

            date_default_timezone_set($settings['server_timezone']);

            return $this;
        }

        /**
         * Returns the default database instance
         *
         * @param string|null $key A specific database ID
         *
         * @return mixed
         */
        public function database($key = null)
        {
            Argument::i()->test(1, 'string', 'null');

            if(is_null($key)) {
                //return the default database
                return $this->defaultDatabase;
            }

            return $this->registry()->get('database', $key);
        }

        /**
         * Returns the root namespace
         *
         * @return Eve\Framework\Index
         */
        public function getRootNamespace()
        {
            return $this->rootNameSpace;
        }

        /**
         * Returns the root namespace
         *
         * @return Eve\Framework\Index
         */
        public function getRouteNamespace()
        {
            return $this->routeNameSpace;
        }

        /**
         * Loads a job
         *
         * @param *string    $key  The job name (which is relative to the class name)
         * @param array|null $data The data to pass to the job
         *
         * @return mixed
         */
        public function job($key, array $data = null)
        {
            Argument::i()->test(1, 'string');

            $name = str_replace(array('-', '_', '/'), ' ', $key);
            $name = ucwords($name);
            $name = str_replace(' ', '\\', $name);

            $class = $this->rootNameSpace.'\\Job\\' . $name;

            //if there's not a class
            if(!class_exists($class)) {
                //throw
                throw new Exception(sprintf(self::NO_JOB, $key));
            }

            //remove starting \\
            $class = substr($class, 1);

            //instantiate the job
            $job = $this->$class();

            //if there is no data
            if(!is_array($data)) {
                //return the job instance
                return $job;
            }

            //there's an array, run the job
            return $job->setData($data)->run();
        }

        /**
         * Returns the current Language
         *
         * @return Eden\Language\Index
         */
        public function language()
        {
            if(is_null($this->defaultLanguage)) {
                $config = $this->settings('config');

                if(!isset($config['i18n'])) {
                    $config['i18n'] = 'en_US';
                }

                $settings = $this->path('settings');
                $path = $settings.'/i18n/'.$config['i18n'].'.php';

                $translations = array();

                if(file_exists($path)) {
                    $translations = $this->settings('i18n/'.$config['i18n']);
                }

                $this->defaultLanguage = $this('language', $translations);
            }

            return $this->defaultLanguage;
        }

        /**
         * Loads a model (not a database model)
         *
         * @param *string $model The model factory key name
         *
         * @return Eve\Framework\Model\Base
         */
        public function model($key)
        {
            Argument::i()->test(1, 'string');

            $class = $this->rootNameSpace.'\\Model\\' . ucwords($key) . '\\Index';

            if(!class_exists($class)) {
                throw new Exception(sprintf(self::NO_MODEL, $key));
            }

            //remove starting \\
            $class = substr($class, 1);

            return $this->$class();
        }

        /**
         * Returns the absolute path
         * given the key
         *
         * @param *string $key The path key name
         *
         * @return string
         */
        public function path($key)
        {
            Argument::i()->test(1, 'string');

            return $this->registry()->get('path', $key);
        }

        /**
         * Gives the ability to add Queues
         *
         * @param *string $task The job key name (which is relative to the class name)
         * @param *array  $data The data to pass to the job
         *
         * @return string
         */
        public function queue($task = null, $data = array())
        {   
            if(is_null($this->defaultQueue)) {
                $config = $this->settings('config');
                $config = $config['queue'];
                $this->defaultQueue = Queue::i(
                    $config['host'],
                    $config['port'],
                    $config['username'],
                    $config['password']);
            }

            return $this->defaultQueue
                ->setTask($task)
                ->setData($data)
                ->setDurable(true)
                ->setApplication(eve()->rootNameSpace);
        }

        /**
         * Browser redirect. We are overloading this
         * to add the root url
         *
         * @param *string $path The path to redirect to
         *
         * @return mixed
         */
        public function redirect($path)
        {
            $args = func_get_args();
            $root = !isset($args[1]) || !$args[1];

            //if it starts with a /
            //and does not start with the root url
            if($root
                && strlen($this->rootUrl) > 0
                && strpos($path, '/') === 0
                && strpos($path, $this->rootUrl.'/') !== 0
            ) {
                //add the root url
                $path = str_replace('//', '/', $this->rootUrl.'/'.$path);
            }

            return parent::redirect($path);
        }

        /**
         * Adds routing middleware
         *
         * @param *string   $method   The request method
         * @param *string   $path     The route pattern
         * @param *function $callback The middleware handler
         *
         * @return Eve\Framework\Index
         */
        public function route($method, $path, $callback)
        {
            Argument::i()
                //argument 1 should be a string
                ->test(1, 'string')
                //argument 2 should be a string
                ->test(2, 'string')
                //argument 3 should be callable
                ->test(3, 'callable');

            //add the root url
            if(strlen($this->rootUrl) > 0) {
                if(strpos($path, $this->rootUrl.'/') !== 0) {
                    $path = str_replace('//', '/', $this->rootUrl.'/'.$path);
                }
            }

            return parent::route($method, $path, $callback);
        }

        /**
         * Returns the current Registry
         *
         * @return Eden\Registry\Index
         */
        public function registry()
        {
            if(!$this->defaultRegistry) {
                $this->defaultRegistry = $this('registry');
            }

            return $this->defaultRegistry;
        }

        /**
         * Set the root namespace before using
         *
         * @param *string $namespace The prescribed root namespace
         *
         * @return Eve\Framework\Index
         */
        public function setRootNamespace($namespace)
        {
            Argument::i()->test(1, 'string');

            $this->rootNameSpace = '\\'.$namespace;

            return $this;
        }

        /**
         * Set the route namespace before using
         *
         * @param *string $namespace The prescribed route namespace
         *
         * @return Eve\Framework\Index
         */
        public function setRouteNamespace($namespace)
        {
            Argument::i()->test(1, 'string');

            $this->routeNameSpace = '\\'.$namespace;

            return $this;
        }

        /**
         * Set the root path before using
         *
         * @param *string $rootPath the path usually from __DIR__
         *
         * @return Eve\Framework\Index
         */
        public function setRootPath($rootPath)
        {
            Argument::i()->test(1, 'string');

            $this->rootPath = $rootPath;

            return $this;
        }

        /**
         * Set the root URL before using
         *
         * @param *string $rootUrl The root URL (in the case this app is not in the site root)
         *
         * @return Eve\Framework\Index
         */
        public function setRootUrl($rootUrl)
        {
            Argument::i()->test(1, 'string');

            //normalize the url
            if(strpos($rootUrl, '/') !== 0) {
                $rootUrl = '/'.$rootUrl;
            }

            $this->rootUrl = $rootUrl;

            return $this;
        }

        /**
         * Returns or saves the settings
         * data given the key
         *
         * @param *string    $key  The settings file base name
         * @param array|null $data The data to set in that name
         *
         * @return Eve\Framework\Index|array
         */
        public function settings($key, array $data = null)
        {
            Argument::i()->test(1, 'string');

            $path = $this->path('settings');
            $file = $path.'/'.$key.'.php';
            
            if(is_array($data)) {
                $this('file')
                    ->set($file)
                    ->setData($data);
                
                return $this;
            }

            if(!file_exists($file)) {
                return array();
            }
            
            //is it already cached?
            if(isset($this->cachedSettings[$file])) {
                return $this->cachedSettings[$file];
            }
            
            //get the data
            $data = $this('file')->set($file)->getData();
            
            //cache the data
            $this->cachedSettings[$file] = $data;
            
            //return the data
            return $data;
        }

        /**
         * Translate string
         *
         * @param *string                  $string The phrase to translate
         * @param array|string[, string..] $args   The sprintf arguments
         *
         * @return string
         */
        public function translate($string, $args = array())
        {
            Argument::i()->test(1, 'string');

            if(!is_array($args)) {
                $args = func_get_args();
                $string = array_shift($args);
            }

            if(count($args)) {
                foreach($args as $i => $arg) {
                    $args[$i] = $this->language()->get($arg);
                }

                return vsprintf($this->language()->get($string), $args);
            }

            return $this->language()->get($string);
        }

        /**
         * Runs the worker.
         *
         * @return Eve\Framwork\Index
         */
        public function work($queue = 'queue')
        {
            if(!eve()->registry()->get($queue)) {
                $config = $this->settings('config');
                $config = $config[$queue];
                echo 'Open Dispatcher'. PHP_EOL;

                $queueConnection = Dispatcher::i(
                    $config['host'],
                    $config['port'],
                    $config['username'],
                    $config['password']
                );

                 eve()->registry()->set($queue, $queueConnection);
            }

            eve()->registry()->get($queue)->run();

            if($this->defaultQueue !== null){
                $this->defaultQueue->getChannel()->close();
                $this->defaultQueue->getConnection()->close();
                echo 'Queue closed'. PHP_EOL;
            }

            return $this;
        }
    }
}
