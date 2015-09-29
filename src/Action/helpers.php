<?php //-->
return array(
	//i18n
	'_' => function($key) {
		$args = func_get_args();
		$key = array_shift($args);
		$options = array_pop($args);
		
		return eve()->translate((string) $key, $args);
	},
	
	//registry
	'registry' => function() {
		$args = func_get_args();
		$options = array_pop($args);
		$data = eve()->registry()->callArray('get', $args);
		
		if(is_object($data) && $data instanceof Eden\Registry\Index) {
			$data = $data->getArray();
		}
		
		if(is_object($data) || is_array($data)) {
			return $options['fn']((array) $data);
		}
		
		return $data;
	},
	
	//create session helpers
	'session' => function($key, $options) {
		if(!isset($_SESSION[$key])) {
			return $options['inverse']();
		}
		
		if(is_object($_SESSION[$key]) || is_array($_SESSION[$key])) {
			return $options['fn']((array) $_SESSION[$key]);
		}
		
		return $_SESSION[$key];
	},
	
	//create query helpers
	'server' => function($key, $options) {
		if(!isset($_SERVER[$key])) {
			return $options['inverse']();
		}
		
		if(is_object($_SERVER[$key]) || is_array($_SERVER[$key])) {
			return $options['fn']((array) $_SERVER[$key]);
		}
		
		return $_SERVER[$key];
	},
	
	//create query helpers
	'query' => function($key, $options) {
		if(!isset($_GET[$key])) {
			return $options['inverse']();
		}
		
		if(is_object($_GET[$key]) || is_array($_GET[$key])) {
			return $options['fn']((array) $_GET[$key]);
		}
		
		return $_GET[$key];
	},
	
	'querystring' => function($options) {
		return http_build_query($_GET);
	},
	
	//create a better if helper
	'when' => function($value1, $operator, $value2, $options) {
		$valid = false;
		
		switch (true) {
			case $operator == 'eq' 	&& $value1 == $value2:
			case $operator == '==' 	&& $value1 == $value2:
			case $operator == 'req' && $value1 === $value2:
			case $operator == '===' && $value1 === $value2:
			case $operator == 'neq' && $value1 != $value2:
			case $operator == '!=' 	&& $value1 != $value2:
			case $operator == 'rneq' && $value1 !== $value2:
			case $operator == '!==' && $value1 !== $value2:
			case $operator == 'lt' 	&& $value1 < $value2:
			case $operator == '<' 	&& $value1 < $value2:
			case $operator == 'lte' && $value1 <= $value2:
			case $operator == '<=' 	&& $value1 <= $value2:
			case $operator == 'gt' 	&& $value1 > $value2:
			case $operator == '>' 	&& $value1 > $value2:
			case $operator == 'gte' && $value1 >= $value2:
			case $operator == '>=' 	&& $value1 >= $value2:
			case $operator == 'and' && ($value1 && $value2):
			case $operator == '&&' 	&& ($value1 && $value2):
			case $operator == 'or' 	&& ($value1 || $value2):
			case $operator == '||' 	&& ($value1 || $value2):
				$valid = true;
				break;
		}
	
		if($valid) {
			return $options['fn']();
		}
	
		return $options['inverse']();
	},
	
	//create a better loop helper
	//fails when using nested loops
	'loop' => function($object, $options) {
		$i = 0;
		$buffer = array();
		$total = count($object);
		
		foreach($object as $key => $value) {
			$buffer[] = $options['fn'](array(
				'key'	=> $key,
				'value'	=> $value,
				'last'	=> ++$i === $total
			));
		}
		
		return implode('', $buffer);
	},
	
	'pagination' => function($total, $range, $options) {
		if($range == 0) {
			return '';
		}
		
		$show = 10;
		$start = 0;
		
		if(isset($_GET['start']) && is_numeric($_GET['start'])) {
			$start = $_GET['start'];
		}
		
		$pages 	= ceil($total / $range);
		$page 	= floor($start / $range) + 1;
		
		$min 	= $page - $show;
		$max 	= $page + $show;
		
		if($min < 1) {
			$min = 1;
		}
		
		if($max > $pages) {
			$max = $pages;
		}
		
		//if no pages
		if($pages <= 1) {
			//return nothing
			return '';
		}
		
		$buffer = array();
		
		for($i = $min; $i <= $max; $i++) {
			$_GET['start'] = ($i -1) * $range;
			
			$buffer[] = $options['fn'](array(
				'href'		=> http_build_query($_GET),
				'active'	=> $i == $page,
				'page'		=> $i
			));
		}
		
		return implode('', $buffer);
	},
	
	//array key
	'in' => function($value, $array, $options) {
		if(is_string($array)) {
			$array = explode(',', $array);
		}
		
		if(!is_array($array)) {
			return $options['inverse']();
		}
		
		if(in_array($value, $array)) {
			return $options['fn']();
		}

		return $options['inverse']();
	},
	
	//offset time helper
	'time' => function($offset, $options) {
		$date = '';
		$offset = preg_replace('/\s/is', $offset);
		
		try {
			eval('$offset = ' . $offset);
			$date = date('Y-m-d', time() + $offset);
		} catch(Exception $e) {}
		
		return $date;
	},
	
	//date helper
	'date' => function($time, $format, $options) {
		return date($format, strtotime($time));
	},
	
	'capital' => function($string, $options) {
		return ucwords($string);
	},
	
	'capitalCamel' => function($string, $options) {
		$string = str_replace('_', ' ', $string);
		$string = ucwords($string);
		$string = str_replace(' ', '', $string);
		
		return $string;
	},
	
	'implode' => function(array $list, $separator, $options) {
		foreach($list as $i => $variable) {
			if(is_string($variable)) {
				$list[$i] = "'".$variable."'";
				continue;
			}
			
			if(is_array($variable)) {
				$list[$i] = "'".implode(',', $variable)."'";
			}
		}
		
		return implode($separator, $list);
	}
);