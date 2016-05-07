<?php //-->
return array(
    //create i18n helpers
    '_' => function($key) {
        $args = func_get_args();
        $key = array_shift($args);
        $options = array_pop($args);

        $more = explode(' __ ', $options['fn']());

		foreach($more as $arg) {
            $args[] = $arg;
        }
		
		foreach($args as $i => $arg) {
			if(is_null($arg)) {
				$args[$i] = '';
			}
		}

        return eve()->translate((string) $key, $args);
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
	
	'number' => function($number, $options) {
		return number_format((float) $number, 0);
	},

	'price' => function($price, $options) {
		return number_format((float) $price, 2);
	},
	
	//create URL helpers
    'root' => function($absolute = false) {
        $root = eve()->rootUrl;

        if($absolute) {
            $protocol = 'http://';
            if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
                $protocol = 'https://';
            } else if($_SERVER['SERVER_PORT'] === 443) {
                $protocol = 'https://';
            }

            $root = $protocol . $_SERVER['HTTP_HOST'] . $root;
        }

        return $root;
    },
	
	//create HTML helpers
    'partial' => function($name) {
        //get args
        $args = func_get_args();

        //get the name ond options
        $name = array_shift($args);

        //get the template root
        $path = eve()->path('template');

        //if the name doesn't have an extension
        if(strpos($name, '.') === false) {
            //make it html
            $name .= '.html';
        }

        //if the name doesn't start with a /
        if(strpos($name, '/') !== 0) {
            //add if
            $name = '/' . $name;
        }

        //if the file does not exist
        if(!file_exists($path.$name)) {
            //return nothing
            return '';
        }

        //yay, get the content
        $contents = file_get_contents($path.$name);

        //we need the options
        $options = array_pop($args);

        //we need the existing context
        $context = $options['handlebars']->getContext();

        //we need a lambda too
        $lambda = $options['handlebars']->getLambdaHelper();

        //if there are still arguments
        if(count($args)) {
            //push it
            $context->push($args[0]);
        }

        //render the results
        $results = $lambda->render($contents);

        //and pop it
        if(count($args)) {
            $context->pop();
        }

        return $results;
    },
	
	'tokenize-partial' => function($name) {
		//get args
        $args = func_get_args();
		
		//get the name
        $name = array_shift($args);
		
		//we need the options
        $options = array_pop($args);
		
		//get the template root
		$path = eve()->path('template');
		
		//name will have quotes
		$name = substr($name, 1, -1);
		
		//if the name doesn't have an extension
		if(strpos($name, '.') === false) {
			//make it html
			$name .= '.html';
		}

		//if the name doesn't start with a /
		if(strpos($name, '/') !== 0) {
			//add /
			$name = '/' . $name;
		}

		//this is the final name
		//which is also the path to the template
		$name = $path . $name;

		//get the partial
		$partial = Eden\Handlebars\Runtime::getPartial($name);
		
		//if there is no partial and file exists
		if(is_null($partial) && file_exists($name)) {
			//this is the partial
			$partial = file_get_contents($name);
			
			//register the partial
			Eden\Handlebars\Runtime::registerPartial($name, $partial);
		}
		
		//prep to call tokenize
		$tokenize = Eden\Handlebars\Runtime::getHelper('tokenize->');
		
		//bind the arguments back
		$options['args'] = str_replace('partial ', '> ', $options['args']);
		
		array_unshift($args, "'" . $name . "'");
		array_push($args, $options);
		
		return call_user_func_array($tokenize, $args);
    },
	
	'strip' => function($html, $options) {
		return strip_tags($html, '<p><b><em><i><strong><b><br><u><ul><li><ol>');
	},
	
	'block' => function($key, $options) {
		$args = func_get_args();
		$options = array_pop($args);
		$key = array_shift($args);
		
		try {
			$block = eve()->block($key);
		} catch(Exception $e) {
			return '';
		}
		
		if(is_scalar($block)) {
			return $block;
		}
		
		if(get_class($block) === 'Closure') {
			try {
				$results = call_user_func_array($block, $args);
			} catch(Exception $e) {
				return '';
			}

			if(is_string($results)) {
				return $results;
			}
			
			return $options['fn']($results);
		}
		
		if(method_exists($block, 'render')) {
			try {
				$results = $block->callArray('render', $args);
			} catch(Exception $e) {
				return '';
			}
			
			if(is_string($results)) {
				return $results;
			}
			
			return $options['fn']($results);
		}
		
		if(method_exists($block, '__toString')) {
			return (string) $block;
		}
		
		return $options['fn']((array) $block);
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

        $pages     = ceil($total / $range);
        $page     = floor($start / $range) + 1;

        $min     = $page - $show;
        $max     = $page + $show;

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
                'href'        => http_build_query($_GET),
                'active'    => $i == $page,
                'page'        => $i
            ));
        }

        return implode('', $buffer);
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

    //create global helpers
    'session' => function($key, $options) {
        if(!isset($_SESSION[$key])) {
            return $options['inverse']();
        }

        if(is_object($_SESSION[$key]) || is_array($_SESSION[$key])) {
            return $options['fn']((array) $_SESSION[$key]);
        }

        return $_SESSION[$key];
    },

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

    'querystring' => function($key = null, $value = '') {
        $query = $_GET;
		
		if(is_scalar($key) && !is_null($key) && isset($query[$key])) {
			$query[$key] = $value;
			$query = http_build_query($query);
			parse_str(urldecode($query), $query);
		}
		
		return http_build_query($query);
    },

    //create a better if helper
    'when' => function($value1, $operator, $value2, $options) {
        $valid = false;

        switch (true) {
            case $operator == 'eq'     && $value1 == $value2:
            case $operator == '=='     && $value1 == $value2:
            case $operator == 'req' && $value1 === $value2:
            case $operator == '===' && $value1 === $value2:
            case $operator == 'neq' && $value1 != $value2:
            case $operator == '!='     && $value1 != $value2:
            case $operator == 'rneq' && $value1 !== $value2:
            case $operator == '!==' && $value1 !== $value2:
            case $operator == 'lt'     && $value1 < $value2:
            case $operator == '<'     && $value1 < $value2:
            case $operator == 'lte' && $value1 <= $value2:
            case $operator == '<='     && $value1 <= $value2:
            case $operator == 'gt'     && $value1 > $value2:
            case $operator == '>'     && $value1 > $value2:
            case $operator == 'gte' && $value1 >= $value2:
            case $operator == '>='     && $value1 >= $value2:
            case $operator == 'and' && ($value1 && $value2):
            case $operator == '&&'     && ($value1 && $value2):
            case $operator == 'or'     && ($value1 || $value2):
            case $operator == '||'     && ($value1 || $value2):
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
		
		if(!is_array($object) && !is_object($object)) {
			return '';
		}

        foreach($object as $key => $value) {
            $buffer[] = $options['fn'](array(
                'first'    => $i === 0,
                'key'    => $key,
                'value'    => $value,
                'last'    => ++$i === $total
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
    },

	'explode' => function($string, $separator, $options) {
		$list = explode($separator, $string);

		return $options['fn'](array('this' => $list));
	},

    //create date helpers
    'time' => function($offset, $options) {
        $date = '';
        $offset = preg_replace('/\s/', '', $offset);

        try {
            eval('$offset = ' . $offset.';');
            $date = date('Y-m-d', time() + $offset);
        } catch(Exception $e) {}

        return $date;
    },

    'date' => function($time, $format, $options) {
        return date($format, strtotime($time));
    },
	
	'relative' => function($date, $options) {
		$settings = eve()->settings('config');
		return eve('timezone', $settings['server_timezone'], $date)->toRelative();
	}
);