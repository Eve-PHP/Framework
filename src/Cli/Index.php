<?php //-->
/*
 * This file is part of the Eve package.
 * (c) 2013-2014 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Eve\Framework\Cli;

/**
 * CLI Interface
 *
 * @package Eve
 */
class Index extends \Eve\Framework\Base
{
	protected $cwd = null;
	
	public function __construct($cwd)
	{
		$this->cwd = $cwd;
	}
	
	public function run(array $args) 
	{
		print PHP_EOL;
		
		$command = 'help';
		
		//as in 'eve generate'
		if(isset($args[1])) {
			$command = $args[1];
		}
		
		$class = '\\Eve\\Framework\\Cli\\'.ucwords($command);
		
		if(!class_exists($class)) {
			self::error('No such command `'.$command.'` found.');
		}
		
		array_shift($args);
		$runner = new $class($this->cwd);
		
		$results = $runner->run($args);
		
		print PHP_EOL;
		
		return $results;
	}
	
	public static function info($message)
	{		
		print sprintf("\033[36m%s\033[0m", '[eve] '.$message);
		print PHP_EOL;
	}
	
	public static function system($message)
	{		
		print sprintf("\033[34m%s\033[0m", '[eve] '.$message);
		print PHP_EOL;
	}
	
	public static function success($message)
	{		
		print sprintf("\033[32m%s\033[0m", '[eve] '.$message);
		print PHP_EOL;
	}
	
	public static function error($message, $die = true)
	{
		print sprintf("\033[31m%s\033[0m", '[eve] '.$message);
		print PHP_EOL;
		
		if($die) {
			print PHP_EOL;
			die(1);
		}
	}
	
	public static function warning($message)
	{
		print sprintf("\033[33m%s\033[0m", '[eve] '.$message);
		print PHP_EOL;
	}
	
	/**
	 * Queries the user for an 
	 * input and returns the results
	 *
	 * @param string
	 * @param string|null
	 * @return string
	 */
	public static function input($question, $default = null) 
	{
		echo $question.': ';
		$handle = fopen ('php://stdin', 'r');
		
		$answer = fgets($handle);
		fclose($handle);
		
		$answer = trim($answer);
		
		if(!$answer) {
			$answer = $default;
		}
		
		return $answer;
	}
}