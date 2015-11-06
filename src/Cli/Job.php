<?php //-->

//activated by: job random-email value=1&value=2

namespace Eve\Framework\Cli;

class Job extends \Eve\Framework\Base
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
		
		\Eve\Framework\Index::i($this->cwd, $namespace)
			// set default paths
			->defaultPaths()
			// set default database
			->defaultDatabases()
			->job($args[1])
			->setData($data)
			->run();
    }
}