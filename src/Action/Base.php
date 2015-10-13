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
 * Action Base
 *
 * @vendor   Eve
 * @package  Framework
 * @author   Christian Blanquera <cblanquera@openovate.com>
 * @standard PSR-2
 */
abstract class Base extends \Eve\Framework\Base
{
    /**
     * @var Eden\Registry\Index $request The request object
     */
    protected $request = null;

    /**
     * @var Eden\Registry\Index $response The response object
     */
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
     * @return string|null|void
     */
    abstract public function render();

    /**
     * Set request
     *
     * @param *Eden\Registry\Index $request The request object
     *
     * @return Eve\Framework\Action\Base
     */
    public function setRequest($request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * Set response
     *
     * @param *Eden\Registry\Index $response The response object
     *
     * @return Eve\Framework\Action\Base
     */
    public function setResponse($response)
    {
        $this->response = $response;
        return $this;
    }
}