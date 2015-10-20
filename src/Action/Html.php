<?php //-->
/**
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
 * @vendor   Eve
 * @package  Framework
 * @author   Christian Blanquera <cblanquera@openovate.com>
 * @standard PSR-2
 */
abstract class Html extends Base
{
    /**
     * @const string TEMPLATE_LAYOUT The default template layout page
     */
    const TEMPLATE_LAYOUT = '_page';

    /**
     * @const string TEMPLATE_EXTENSION The default template extension
     */
    const TEMPLATE_EXTENSION = 'html';

    /**
     * @var string|null $engine The template rendering engine
     */
    protected $engine = null;

    /**
     * @var string|null $layout The default template extension
     */
    protected $layout = self::TEMPLATE_LAYOUT;

    /**
     * @var string|null $id This is the HTML tag class name
     */
    protected $id = null;

    /**
     * @var string|null $title This is the page title
     */
    protected $title = null;

    /**
     * @var string|null $template This is the default template body file
     */
    protected $template = null;

    /**
     * @var array $meta HTML meta tags data
     */
    protected $meta = array();

    /**
     * @var array $meta HTML link tags data
     */
    protected $links = array();

    /**
     * @var array $styles HTML style tags data
     */
    protected $styles = array();

    /**
     * @var array $scripts HTML script tags data
     */
    protected $scripts = array();

    /**
     * @var array $messages Messages passed from previous page via session
     */
    protected $messages = array();

    /**
     * @var array $head Data passed to the _head partial
     */
    protected $head = array();

    /**
     * @var array $head Data passed to the body template
     */
    protected $body = array();

    /**
     * @var array $head Data passed to the _foot partial
     */
    protected $foot = array();

    /**
     * Transform block to string
     *
     * @param *string $template The relative template body file
     *
     * @return string
     */
    protected function build($template)
    {
        $this->body['flash'] = array();
        if(isset($_SESSION['flash']['message'])) {
            $this->body['flash']['message'] = $_SESSION['flash']['message'];
            $this->body['flash']['type'] = $_SESSION['flash']['type'];
            unset($_SESSION['flash']);
        }

        $body = $this->parse($template, $this->body);

        $page = array(
            'flash' => $this->body['flash'],
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
     * @param string|null $message Any message to pass to the page
     * @param mixed       $errors  If string will redirect otherwise pass this to page
     * @param array       $item    Existing item data (for forms)
     *
     * @return string
     */
    protected function fail(
        $message = null,
        $errors = array(),
        array $items = array()
    ) {
        if($message) {
            $_SESSION['flash']['message'] = $message;
            $_SESSION['flash']['type'] = 'danger';
        }

        //if it's a string
        if(is_string($errors)) {
            //redirect will forcefully exit
            return (string) eve()->redirect($errors);
        }

        $this->body['errors'] = $errors;
        $this->body['item'] = $items;

        $this->trigger('html-fail', $this, $message, $errors, $items);
        $this->trigger('response-fail', $this, $message, $errors, $items);

        return $this->build($this->getTemplate());
    }

    /**
     * Returns the default template engine
     *
     * @return Eden\Handlebars\Index
     */
    public function getEngine()
    {
        if($this->engine) {
            return $this->engine;
        }

        //get the template path
        $path = eve()->path('template');

        //create engine
        $this->engine = eve('handlebars');

        //add helpers
        $helpers = include(__DIR__.'/helpers.php');

        foreach($helpers as $name => $callback) {
            $this->engine->registerHelper($name, $callback);
        }

        return $this->engine;
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
            //Action folder and template folder
            //are in the same directoryby default

            // Sample\Namespace\Action\Can\Be\Anywhere
            $index = 'Action\\';
            $root = strpos(get_class($this), $index);
            $root += strlen($index);

            $this->template = eve('string')
                // \Can\Be\Anywhere
                ->set('\\'.substr(get_class($this), $root))
                // /Can/Be/Anywhere
                ->str_replace('\\', DIRECTORY_SEPARATOR)
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
     * @param string      $file    The relative template file
     * @param array       $data    Data to bind to the template
     * @param string|null $trigger Event trigger to fire
     *
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

        $path = eve()->path('template');

        if(file_exists($path.'/'.$file.'.php')
            && static::TEMPLATE_EXTENSION !== 'php'
        ) {
            return eve('template')->set($data)->parsePHP($path.'/'.$file.'.php');
        } else if(file_exists($path.$file.'.phtml')
            && static::TEMPLATE_EXTENSION !== 'phtml'
        ) {
            return eve('template')->set($data)->parsePHP($path.'/'.$file.'.phtml');
        }

        $contents = file_get_contents($path . '/'. $file . '.' . static::TEMPLATE_EXTENSION);
        $template = $this->getEngine()->compile($contents);

        return $template($data);
    }

    /**
     * Transform block to string
     *
     * @param string|null $message Any message to pass to the page
     * @param mixed       $results If string will redirect otherwise pass this to page
     *
     * @return string
     */
    protected function success($message = null, $results = null)
    {
        if($message) {
            $_SESSION['flash']['message'] = $message;
            $_SESSION['flash']['type'] = 'success';
        }

        $this->trigger('html-success', $this, $message, $results);
        $this->trigger('response-success', $this, $message, $results);

        if(is_string($results)) {
            //redirect will forcefully exit
            return (string) eve()->redirect($results);
        }

        if(!is_null($results)) {
            $this->body['results'] = $results;
        }

        return $this->build($this->getTemplate());
    }
}