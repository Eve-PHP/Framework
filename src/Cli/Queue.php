<?php //-->

//activated by: job random-email value=1&value=2

namespace Eve\Framework\Cli;

class Queue extends \Eve\Framework\Base
{
    /**
     * @var string|null $cwd The path from where this was called
     */
    protected $cwd = null;

    /**
     * We need the CWD
     *
     * @param string $cwd The path from where this was called
     */
    public function __construct($cwd)
    {
        $this->cwd = $cwd;
    }

    /**
     * Runs the CLI process
     *
     * @param array $args CLI arguments
     *
     * @return mixed
     */
    public function run(array $args)
    {
		if(count($args) < 3) {
            Index::error('Not enough arguments.', 'Usage: eve job random-mail subject=hi&body=hello...');
        }
		
		$data = array();
		
		if(strpos($args[2], '?') === 0) {
			parse_str(substr($args[2], 1), $data);
		} else {
			$data = json_decode($args[2], true);
		}
		
		$namespace = 'Eve';
		
		if(file_exists($this->cwd.'/composer.json')) {
			$json = $this('file', $this->cwd.'/composer.json')->getContent();
			$json = json_decode($json, true);

			if(isset($json['autoload']['psr-4'])
				&& is_array($json['autoload']['psr-4'])
			) {
				foreach($json['autoload']['psr-4'] as $namespace => $path) {
					if(strlen($path) === 0) {
						$namespace = substr($namespace, 0, -1);
						break;
					}
				}
			}
		}
		
		$queue = \Eve\Framework\Index::i($this->cwd, $namespace)
			// set default paths
			->defaultPaths()
			// set default database
			->defaultDatabases()
			->queue($args[1])
			->setData($data);
			
		if(isset($args[3]) && is_string($args[3])) {
			$queue->setPriority(trim($args[3]));
		} else if(isset($args[3]) && is_numeric($args[3])) {
			$queue->setPriority($args[3]);
		}
		
		if(isset($args[4]) && is_numeric($args[4])) {
			$queue->setDelay($args[4]);
		}
		
		$queue->save();
		
		Index::success('`'.$args[1].'` has been successfully queued.');
    }
}