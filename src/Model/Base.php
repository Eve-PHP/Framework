<?php //-->
/**
 * This file is part of the Eve Framework Library
 * (c) 2014-2016 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE
 * distributed with this package.
 */

namespace Eve\Framework\Model;

/**
 * Model base class
 *
 * @vendor   Eve
 * @package  Framework
 * @author   Christian Blanquera <cblanquera@openovate.com>
 * @standard PSR-2
 */
class Base extends \Eve\Framework\Base
{
    /**
     * @const string FAIL_406 Error template
     */
    const FAIL_406 = 'Invalid Parameters';

    /**
     * @const string INVALID_ID Error template
     */
    const INVALID_ID = 'Invalid ID';

    /**
     * @const string INVALID_REQUIRED Error template
     */
    const INVALID_REQUIRED = 'Cannot be empty';

    /**
     * @const string INVALID_EMPTY Error template
     */
    const INVALID_EMPTY = 'Cannot be empty, if set';

    /**
     * @const string INVALID_ONEOF Error template
     */
    const INVALID_ONEOF = 'Must be one of %s';

    /**
     * @const string INVALID_EMAIL Error template
     */
    const INVALID_EMAIL = 'Should be a valid email';

    /**
     * @const string INVALID_HEX Error template
     */
    const INVALID_HEX = 'Should be valid hexidecimal';

    /**
     * @const string INVALID_CC Error template
     */
    const INVALID_CC = 'Should be a valid credit card';

    /**
     * @const string INVALID_HTML Error template
     */
    const INVALID_HTML = 'Should be valid HTML';

    /**
     * @const string INVALID_URL Error template
     */
    const INVALID_URL = 'Should be a valid url';

    /**
     * @const string INVALID_SLUG Error template
     */
    const INVALID_SLUG = 'Should be only alpha-numeric optional hyphens';

    /**
     * @const string INVALID_JSON Error template
     */
    const INVALID_JSON = 'Should be valid JSON';

    /**
     * @const string INVALID_DATE Error template
     */
    const INVALID_DATE = 'Should be a valid date (YYYY-MM-DD)';

    /**
     * @const string INVALID_TIME Error template
     */
    const INVALID_TIME = 'Should be a valid time (HH:MM:SS)';

    /**
     * @const string INVALID_REGEX Error template
     */
    const INVALID_REGEX = 'Invalid Format';

    /**
     * @const string INVALID_ALPHANUM Error template
     */
    const INVALID_ALPHANUM = 'Should be only alpha-numeric';

    /**
     * @const string INVALID_ALPHANUM_HYPHEN Error template
     */
    const INVALID_ALPHANUM_HYPHEN = 'Should be only alpha-numeric optional hyphens';

    /**
     * @const string INVALID_ALPHANUM_SCORE Error template
     */
    const INVALID_ALPHANUM_SCORE = 'Should be only alpha-numeric optional underscore';

    /**
     * @const string INVALID_ALPHANUM_LINE Error template
     */
    const INVALID_ALPHANUM_LINE = 'Should be only alpha-numeric optional hyphens or underscore';

    /**
     * @const string INVALID_BOOL Error template
     */
    const INVALID_BOOL = 'Should either be 0 or 1';

    /**
     * @const string INVALID_SMALL Error template
     */
    const INVALID_SMALL = 'Should be between 0 and 9';

    /**
     * @const string INVALID_INT Error template
     */
    const INVALID_INT = 'Should be a valid integer';

    /**
     * @const string INVALID_FLOAT Error template
     */
    const INVALID_FLOAT = 'Should be a valid floating point';

    /**
     * @const string INVALID_NUMBER Error template
     */
    const INVALID_NUMBER = 'Should be a valid number';

    /**
     * @const string INVALID_PRICE Error template
     */
    const INVALID_PRICE = 'Should be a valid price';

    /**
     * @const string INVALID_GT Error template
     */
    const INVALID_GT = 'Should be greater than %s';

    /**
     * @const string INVALID_GTE Error template
     */
    const INVALID_GTE = 'Should be greater than or equal to %s';

    /**
     * @const string INVALID_LT Error template
     */
    const INVALID_LT = 'Should be less than %s';

    /**
     * @const string INVALID_LTE Error template
     */
    const INVALID_LTE = 'Should be less than or equal to %s';

    /**
     * @const string INVALID_SGT Error template
     */
    const INVALID_SGT = 'Should be greater than %s letters';

    /**
     * @const string INVALID_SGTE Error template
     */
    const INVALID_SGTE = 'Should be greater than or equal to %s letters';

    /**
     * @const string INVALID_SLT Error template
     */
    const INVALID_SLT = 'Should be less than %s letters';

    /**
     * @const string INVALID_SLTE Error template
     */
    const INVALID_SLTE = 'Should be less than or equal to %s letters';

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