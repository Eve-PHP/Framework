<?php //-->

//activated by: install

namespace Eve\Framework\Cli;

class Install extends \Eve\Framework\Base
{
	protected $cwd = null;
	protected $file = null;
	
	/**
	 * We need the CWD and the Schema
	 *
	 * @param string
	 */
	public function __construct($cwd) 
	{
		$this->cwd = $cwd;
	}
	
	/**
	 * Tries to gather what we need for this task to run
	 *
	 * @param array
	 * @return this
	 */
	public function setup($args) 
	{
		if(!class_exists('\\ZipArchive')) {
			Index::error('Install needs the ZipArchive class. '
			. 'See: http://php.net/manual/en/class.ziparchive.php');
		}
		
		if(!is_dir($this->cwd . '/tmp')) {
			mkdir($this->cwd . '/tmp');
		}
		
		$this->file = fopen($this->cwd . '/tmp/framework.zip', 'w');
		
		return $this;
	}
	
	/**
	 * Runs the CLI Generate process
	 *
	 * @return void
	 */
	public function run($args) 
	{
		$this->setup($args);
		
		Index::info('Downloading files..');	
		
		$this('curl')
			->setUrl('https://github.com/Openovate/Framework/archive/v4.zip')
			->setFile($this->file)
			->setFollowLocation(true)
			->send();
		
		fclose($this->file);
		
		Index::info('Extracting files..');
		
		try {
			$zip = new \ZipArchive();
			$resource = $zip->open($this->cwd . '/tmp/framework.zip');
			
			if(!$resource) {
				throw new \Exception('Cannot extract data. Aborting.');
			}
			
			$zip->extractTo($this->cwd.'/tmp');
			$zip->close();
			
			Index::info('Copying files..');
			
			$root = $this->cwd.'/tmp/Framework-4';
			$files = $this('folder', $root)->getFiles(null, true);
			
			foreach($files as $file) {
				$destination = str_replace('/tmp/Framework-4', '', $file->get());
				$folder = $this('file', $destination)->getFolder();
				
				if(!is_dir($folder)) {
					mkdir($folder, 0777, true);
				}
				
				copy($file->get(), $destination);
			}
		} catch(\Exception $e) {
			Index::error($e->getMessage(), false);	
		}
		
		Index::info('Cleaning up ..');
		
		$tmp = $this('folder', $this->cwd . '/tmp');
		
		$files = $tmp->getFiles(null, true);
		$folders = $tmp->getFolders(null, true);
		$folders = array_reverse($folders);
		
		
		foreach($files as $file) {
			$file->remove();
		}
		
		foreach($folders as $folder) {
			$folder->remove();
		}
		
		$tmp->remove();
		Index::success('framework installation complete!');
		
		die(0);
	}
}