<?php //-->
/**
 * This file is part of the Eve Framework Library
 * (c) 2014-2016 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE
 * distributed with this package.
 */

namespace Eve\Framework\Job;

/**
 * Job base class
 *
 * @vendor   Eve
 * @package  Framework
 * @author   Christian Blanquera <cblanquera@openovate.com>
 * @standard PSR-2
 */
abstract class Base extends \Eve\Framework\Base
{
    /**
     * @var array|null $data Data needed for the job
     */
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
     * @param mixed $data Data needed for the job
     *
     * @return Eve\Framework\Job\Base
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }
}