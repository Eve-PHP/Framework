<?php //-->
/*
 * This file is part of the Eve package.
 * (c) 2013-2014 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Eve\Framework;

/**
 * Error handler template class
 *
 * @vendor   Eve
 * @package  Framework
 * @author   Christian Blanquera <cblanquera@openovate.com>
 * @standard PSR-2
 */
class Error extends Base
{
    /**
     * Output the error details in HTML
     *
     * @param *string $type    The error type
     * @param *string $level   The error level
     * @param *string $class   The class where the error came from
     * @param *string $file    The file where the error came from
     * @param *string $line    The line where the error came from
     * @param *string $message The error message
     * @param *array  $trace   The back trace
     * @param *int    $offset  To only report a subset of the back trace
     *
     * @return void
     */
    public function htmlDetails(
        $type,
        $level,
        $class,
        $file,
        $line,
        $message,
        $trace,
        $offset)
    {
        $history = array();
        for(; isset($trace[$offset]); $offset++) {
            $row = $trace[$offset];

            //lets formulate the method
            $method = $row['function'].'()';
            if(isset($row['class'])) {
                $method = $row['class'].'->'.$method;
            }

            $rowLine = isset($row['line']) ? $row['line'] : 'N/A';
            $rowFile = isset($row['file']) ? $row['file'] : 'Virtual Call';

            //add to history
            $history[] = sprintf('%s File: %s Line: %s', $method, $rowFile, $rowLine);
        }

        $message = sprintf(
            '%s %s: "%s" from %s in %s on line %s',
            $type,         $level,     $message,
            $class,     $file,         $line);

        return implode("<br />", array(
            '<h4>'.$message.'</h4>',
            implode("<br />", $history)
        ));
    }

    /**
     * Output the generic error in HTML
     *
     * @param *string $type    The error type
     * @param *string $level   The error level
     * @param *string $class   The class where the error came from
     * @param *string $file    The file where the error came from
     * @param *string $line    The line where the error came from
     * @param *string $message The error message
     * @param *array  $trace   The back trace
     * @param *int    $offset  To only report a subset of the back trace
     *
     * @return void
     */
    public function htmlGeneric(
        $type,
        $level,
        $class,
        $file,
        $line,
        $message,
        $trace,
        $offset)
    {
        return '<h1>A server Error occurred</h1>';
    }

    /**
     * Output the error details in JSON
     *
     * @param *string $type    The error type
     * @param *string $level   The error level
     * @param *string $class   The class where the error came from
     * @param *string $file    The file where the error came from
     * @param *string $line    The line where the error came from
     * @param *string $message The error message
     * @param *array  $trace   The back trace
     * @param *int    $offset  To only report a subset of the back trace
     *
     * @return void
     */
    public function jsonDetails(
        $type,
        $level,
        $class,
        $file,
        $line,
        $message,
        $trace,
        $offset)
    {
        $history = array();
        for(; isset($trace[$offset]); $offset++) {
            $row = $trace[$offset];

            //lets formulate the method
            $method = $row['function'].'()';
            if(isset($row['class'])) {
                $method = $row['class'].'->'.$method;
            }

            $rowLine = isset($row['line']) ? $row['line'] : 'N/A';
            $rowFile = isset($row['file']) ? $row['file'] : 'Virtual Call';

            //add to history
            $history[] = sprintf('%s File: %s Line: %s', $method, $rowFile, $rowLine);
        }

        $message = sprintf(
            '%s %s: "%s" from %s in %s on line %s',
            $type,         $level,     $message,
            $class,     $file,         $line);

        return json_encode(array(
            'error'     => true,
            'message'    => $message,
            'trace'        => $history),
            JSON_PRETTY_PRINT);
    }

    /**
     * Output the generic error in JSON
     *
     * @param *string $type    The error type
     * @param *string $level   The error level
     * @param *string $class   The class where the error came from
     * @param *string $file    The file where the error came from
     * @param *string $line    The line where the error came from
     * @param *string $message The error message
     * @param *array  $trace   The back trace
     * @param *int    $offset  To only report a subset of the back trace
     *
     * @return void
     */
    public function jsonGeneric(
        $type,
        $level,
        $class,
        $file,
        $line,
        $message,
        $trace,
        $offset)
    {
        return json_encode(array(
            'error'     => true,
            'message'    => 'A server Error occurred'),
            JSON_PRETTY_PRINT);
    }

    /**
     * Output the error details in plain text
     *
     * @param *string $type    The error type
     * @param *string $level   The error level
     * @param *string $class   The class where the error came from
     * @param *string $file    The file where the error came from
     * @param *string $line    The line where the error came from
     * @param *string $message The error message
     * @param *array  $trace   The back trace
     * @param *int    $offset  To only report a subset of the back trace
     *
     * @return void
     */
    public function plainDetails(
        $type,
        $level,
        $class,
        $file,
        $line,
        $message,
        $trace,
        $offset)
    {
        $history = array();
        for(; isset($trace[$offset]); $offset++) {
            $row = $trace[$offset];

            //lets formulate the method
            $method = $row['function'].'()';
            if(isset($row['class'])) {
                $method = $row['class'].'->'.$method;
            }

            $rowLine = isset($row['line']) ? $row['line'] : 'N/A';
            $rowFile = isset($row['file']) ? $row['file'] : 'Virtual Call';

            //add to history
            $history[] = sprintf('%s File: %s Line: %s', $method, $rowFile, $rowLine);
        }

        $message = sprintf(
            '%s %s: "%s" from %s in %s on line %s',
            $type,         $level,     $message,
            $class,     $file,         $line);

        return implode("\n\n", array(
            $message,
            implode("\n", $history)
        ));
    }

    /**
     * Output the generic error in plain text
     *
     * @param *string $type    The error type
     * @param *string $level   The error level
     * @param *string $class   The class where the error came from
     * @param *string $file    The file where the error came from
     * @param *string $line    The line where the error came from
     * @param *string $message The error message
     * @param *array  $trace   The back trace
     * @param *int    $offset  To only report a subset of the back trace
     *
     * @return void
     */
    public function plainGeneric(
        $type,
        $level,
        $class,
        $file,
        $line,
        $message,
        $trace,
        $offset)
    {
        return 'A server Error occurred';
    }
}