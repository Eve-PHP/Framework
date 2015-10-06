<?php //-->

//activated by: install

namespace Eve\Framework\Cli;

class Install extends \Eve\Framework\Base
{
	protected $cwd = null;
	protected $file = null;
	protected $databases = array(
		'default' => array(
			'host' 		=> '127.0.0.1',
			'name' 		=> 'eve_salaaap',
			'user' 		=> 'root',
			'pass' 		=> '',
			'type' 		=> 'mysql',
			'default' 	=> true));
	
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
		
		$this->databases['default']['host'] = Index::input(
			'What is your database host (127.0.0.1)', 
			'127.0.0.1');
			
		$this->databases['default']['name'] = Index::input(
			'What is your database name (eve_framework)', 
			'eve_framework');
		
		$this->databases['default']['user'] = Index::input(
			'What is your database username ? (root)', 
			'root');
		
		$this->databases['default']['pass'] = Index::input(
			'What is your database password ? (<nothing>)', 
			'');
		
		Index::error('Thanks. '. floor(rand() % 1000) .' questions to go..', false);
		
		Index::input('Continue ? (yes)');
		
		Index::success('Just kidding, we are good to go :)');
		
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
			->setUrl('https://github.com/Eve-PHP/Shade/archive/master.zip')
			->setFile($this->file)
			->setFollowLocation(true)
			->setSslVerifyPeer(false)
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
			
			$root = $this->cwd.'/tmp/Shade-master';
			$files = $this('folder', $root)->getFiles(null, true);
			
			foreach($files as $file) {
				$destination = str_replace('/tmp/Shade-master', '', $file->get());
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
		
		Index::info('Copying settings..');	
		$this('file', $this->cwd.'/settings/databases.php')->setData($this->databases);
		copy($this->cwd.'/settings/sample.config.php', $this->cwd.'/settings/config.php');
		copy($this->cwd.'/settings/sample.test.php', $this->cwd.'/settings/test.php');
		
		Index::info('Creating database..');	
		
		$this->install();
		
		Index::success('Database created.');	
		
		Index::warning('Please set the configs in the settings folder');
		Index::system('Control Login is: admin@openovate.com / admin');
		Index::success('Framework installation complete!');
		
		die(0);
	}
	
	public function install()
	{
		$build = $this('mysql',
			$this->databases['default']['host'],
			'',
			$this->databases['default']['user'],
			$this->databases['default']['pass']);
			
		$main = $this('mysql',
			$this->databases['default']['host'],
			$this->databases['default']['name'],
			$this->databases['default']['user'],
			$this->databases['default']['pass']);
			
		$build->query('DROP DATABASE IF EXISTS `'.$this->databases['default']['name'].'`');
		$build->query('CREATE DATABASE `'.$this->databases['default']['name'].'`');
		
		//get schema
		$schema = $this('file', $this->cwd.'/schema.sql')->getContent();
		
		//add queries
		$queries = explode(';', $schema);
		
		$queries[] = "INSERT INTO `app` (
			`app_id`, 
			`app_name`, 
			`app_domain`, 
			`app_token`, 
			`app_secret`, 
			`app_permissions`, 
			`app_website`, 
			`app_active`, 
			`app_type`, 
			`app_flag`, 
			`app_created`, 
			`app_updated`
		) VALUES (
			1, 
			'Main Application', 
			'*.openovate.com', 
			'986e7ce6bec660838491c1cd0a1f4ef6', 
			'ba0d2fc7aab09dfa3463943c0aaa8551', 
			'".implode(',', array(
				'public_profile',
				'public_sso',
				'personal_profile',
				'user_profile'
			))."', 
			'http://openovate.com/', 
			1, NULL, 0, '2015-08-21 00:00:00', '2015-08-21 00:00:00'
		)";
		
		$queries[] = "INSERT INTO `auth` (
			`auth_id`, 
			`auth_slug`, 
			`auth_password`, 
			`auth_token`, 
			`auth_secret`, 
			`auth_permissions`, 
			`auth_facebook_token`, 
			`auth_facebook_secret`, 
			`auth_linkedin_token`, 
			`auth_linkedin_secret`, 
			`auth_twitter_token`, 
			`auth_twitter_secret`, 
			`auth_google_token`, 
			`auth_google_secret`, 
			`auth_active`, 
			`auth_type`, 
			`auth_flag`, 
			`auth_created`, 
			`auth_updated`
		) VALUES (
			1, 
			'admin@openovate.com', 
			MD5('admin'), 
			'986e7ce6bec660838491c1cd0a1f4ef6', 
			'ba0d2fc7aab09dfa3463943c0aaa8551', 
			'".implode(',', array(
				'public_profile',
				'public_sso',
				'personal_profile',
				'user_profile'
			))."', 
			NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 0, 
			'2015-09-11 23:05:17', '2015-09-11 23:05:17'
		)";
		
		$queries[] = "INSERT INTO `profile` (
			`profile_id`, 
			`profile_name`, 
			`profile_email`, 
			`profile_phone`, 
			`profile_detail`, 
			`profile_company`, 
			`profile_job`, 
			`profile_gender`, 
			`profile_birth`, 
			`profile_website`, 
			`profile_facebook`, 
			`profile_linkedin`, 
			`profile_twitter`, 
			`profile_google`, 
			`profile_active`, 
			`profile_type`, 
			`profile_flag`, 
			`profile_created`, 
			`profile_updated`
		) VALUES (
			1, 
			'Admin', 
			'admin@openovate.com', 
			'+63 (2) 654-5110', 
			NULL, NULL, NULL, NULL, NULL, 
			NULL, NULL, NULL, NULL, NULL, 1, NULL, 
			0, '2015-09-11 23:05:16', '2015-09-11 23:05:16')";
		
		$queries[] = "INSERT INTO `app_profile` (`app_profile_app`, `app_profile_profile`) VALUES (1, 1)";
		$queries[] = "INSERT INTO `auth_profile` (`auth_profile_auth`, `auth_profile_profile`) VALUES (1, 1)";
		
		//now call the queries
		foreach($queries as $query) {
			$lines = explode("\n", $query);
			
			foreach($lines as $i => $line) {
				if(strpos($line, '--') === 0 || !trim($line)) {
					unset($lines[$i]);
					continue;
				}
			}
			
			$query = trim(implode("\n", $lines));
			
			if(!$query) {
				continue;
			}
			
			$main->query($query);
		}
	}
}