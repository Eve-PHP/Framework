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

    /**
     * Make everything into a string
     * remove empty strings
     *
     * @param array $item The item to prepare
     *
     * @return array
     */
    public function prepare($item)
    {
        $prepared = array();

        foreach($item as $key => $value) {
            //if it's null
            if($value === null) {
                //set it and continue
                $prepared[$key] = null;
                continue;
            }

            //if is array
            if(is_array($value)) {
                //recursive
                $prepared[$key] = $this->prepare($value);
                continue;
            }

            //if it can be converted
            if(is_scalar($value)) {
                $prepared[$key] = (string) $value;
                continue;
            }

            //we tried our best ...
            $prepared[$key] = $value;
        }

        return $prepared;
    }
}