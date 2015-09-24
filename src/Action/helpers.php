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
			$data = $data->get(false);
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
	
			case $operator == 'startsWith'
			&& strpos($value1, value2) === 0:
	
			case operator == 'endsWith'
			&& strpos($value1, $value2) === (strlen($value1) - strlen($value2)):
				$valid = true;
				break;
		}
	
		if($valid) {
			return $options['fn']();
		}
	
		return $options['inverse']();
	},
	
	//create a better loop helper
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
	
	//array key
	'in' => function(array $array, $key, $options) {
		if(!isset($array[$key])) {
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
	}
);