# Writing Tests

It's important to understand that this framework is not responsible for writing your test code (though the generators can create it for you). You should always review and write custom tests according to the exact features of your app. In Eve, we use several methods to ensure that our custom projects are stable, found below, and we run all tests using `phpunit`.

 - Unit Tests
 - Functional Tests
 - UA Tests
 - Code Coverage
 - Coding Standards
 
### Install PHPUnit

This is so you can run unit tests on your project.

```
curl -OL https://phar.phpunit.de/phpunit.phar

mv chmod +x phpunit.phar

mv phpunit.phar /usr/local/bin/phpunit

```

### Install PHP_Codesniffer

This is so you can be compliant to PSR-2

```
curl -OL https://squizlabs.github.io/PHP_CodeSniffer/phpcs.phar

mv chmod +x phpcs.phar

mv phpunit.phar /usr/local/bin/phpcs

```

### Install PHP_Codesniffer CBF

This is for autofixing PSR-2 violations

```
curl -OL https://squizlabs.github.io/PHP_CodeSniffer/phpcbf.phar

mv chmod +x phpcbf.phar

mv phpunit.phar /usr/local/bin/phpcbf

```
 
## Unit Tests

Though very abstract, this framework was methodically thought about to reduce the amount of unit tests you would normally have to write. Models have 2 formats, the index and the CRUD. The CRUD has 2 main methods called `errors()` and `process()`. Jobs only have 1 main method to test called `run()` and actions have 2 main methods called `render()` and `check()`. Unit tests are performed on the following methods.

 - `errors()` - Found in Model CRUD
 - `process()` - Found in Model CRUD
 - All methods in Model/[MODEL NAME]/Index.php
 - 'run()' - Found in Job folder
 
For all methods above we write tests which first tests what happens if we input invalid data (or empty data) and what happens if we input valid data. An example of this is found below.

```
class Eve_Model_Auth_Create_Test extends PHPUnit_Framework_TestCase
{
    public function testErrors() 
    {
		//invalid
        $errors = eve()->model('auth')->create()->errors();

        $this->assertEquals('Cannot be empty', $errors['auth_slug']);
        $this->assertEquals('Cannot be empty', $errors['auth_permissions']);
        $this->assertEquals('Cannot be empty', $errors['auth_password']);
        $this->assertEquals('Cannot be empty', $errors['confirm']);
		
		//valid
		$now = explode(" ", microtime());
		
        $errors = eve()->model('auth')->create()->errors(array(
			'auth_slug' => 'TEST AUTH ' + $now[1],
			'auth_permissions' => 'test_permissions_1,test_permissions_2',
			'auth_password'    => '123456',
			'confirm' => '123456',
		));

        $this->assertTrue(count($errors) === 0);
    }
    
    public function testProcess() 
    {
		//invalid
		$error = false;
		try {
			$model = eve()
				->model('auth')
				->create()
				->process();
		} catch(Exception $e) {
			$error = true;
		}		
		
		$this->assertTrue($error);
		
		//valid
        $now = explode(" ", microtime());

        $model = eve()
            ->model('auth')
            ->create()
            ->process(array(
                'auth_slug' => 'TEST AUTH ' + $now[1],
                'auth_permissions' => 'test_permissions_1,test_permissions_2',
                'auth_password'    => '123456',
                'confirm' => '123456' ));

        $this->assertTrue(is_numeric($model['auth_id']));
        eve()->registry()->set('test', 'auth', $model->get());
    }
}
```

## Functional Tests

Functional Tests are performed on all action classes. In functional tests we do not emulate a browser per se. The best way to test actions is to force inject data into the global variables `$_SERVER`, `$_POST`, `$_GET`, `$_SESSION`, etc. There are 3 cases we need to test for.
 
 - What happens when the action is rendered ?
 - What happens when we submit invalid data ?
 - What happens when we submit valid data ?

An example of this kind of test is found below. Eve also comes with an easy way to force inject data into PHP's global variables using a scaffold called `BrowserTest`.

```
class App_Back_Action_App_Create_Test extends PHPUnit_Framework_TestCase
{
    public function setUp() {
         BrowserTest::i()->setTemplate('back');
    }

    public function testRender()
    {
        $results = BrowserTest::i()
            ->setPath('/back/action/app/create')
            ->setMethod('GET')
            ->setIsTriggered(false)
            ->process();

        $this->assertContains('Create App', $results['data']);
    }
    
    public function testInvalid()
    {
        $data = array(
            'app_name' => 'Test Back App Create',
            'app_permissions' => 'public_sso,user_profile,global_profile',
        );

        $results = BrowserTest::i()
            ->setPath('/back/action/app/create')
            ->setPost($data)
            ->setIsValid(false)
            ->setIsTriggered(true)
            ->process();
        
        $this->assertFalse($results['triggered']);
        $this->assertContains('Cannot be empty', $results['data']);
    }
    
    public function testValid()
    {
        $data = array(
            'app_name' => 'Test Back App Create',
            'app_domain' => '*.test.com',
            'app_permissions' => 'public_sso,user_profile,global_profile', 
            'profile_id' => $_SESSION['me']['profile_id']
        );

        $results = BrowserTest::i()
            ->setPath('/back/action/app/create')
            ->setPost($data)
            ->setIsValid(true)
            ->setIsTriggered(true)
            ->process();
        
        $this->assertTrue($results['triggered']);
    }
}
```

## UA Tests

For User Acceptance Testing, there are two ways a business can do it. The first traditional way, however impractical, is staffing a Quality Assurance team to test the application everyday. The other way is creating an automated UAT tool that incorporates to our test suites. In Eve, we use [Selenium](http://seleniumhq.com). In PHP Unit version 2, Selenium drivers are included by default. Tests are created using the *Selenium Browser Plugin* and converted to directly to PHP Unit code, in which we just need to paste it and include it into our test suites. A sample QA process would look like the following post launch.

 - Team lead creates the test by recording the indended UI actions.
    - All should be success cases.
	- All cases should be based on a single respective user story. 
 - Team lead saves all tests separated by files into a folder.
 - Team lead sends to development team to include into the test suites.
 - Development team includes the tests in `phpunit.xml`.
 - Development team runs the tests to ensure it is infact working.
 
Contrirary to what others believe, UA tests should not be organized on a per page basis. This is because proper issues are reported as use cases rather than an entire page not working.

Before running tests with `phpunit`, they need to download and install selenium server. The following describes how this can be acheived.

```
curl -OL https://selenium-release.storage.googleapis.com/2.45/selenium-server-standalone-2.45.0.jar

mv selenium-server-standalone-2.45.0.jar ./selenium

java -jar selenium

``` 

When the Development Team now runs this test, a browser will open up and perform these tests. This isn't ideal for a build server, because a build server does not have Google Chrome or Firefox like a human operating system has. To run selenium on a build server we need to download and set `phantomjs` as the browser. Read up on [integrating PhantomJS with PHPUnit and Selenium](https://google.com/?q=phantomjs+phpunit+selenium). An example of updating the selenium tests that was given from the Team Lead to the Development Team would simply look like this.

```
protected function setUp() 
{
	$this->setBrowser('phantomjs');
}
```

Writing effective tests makes more sense when incorporating a Coninuous Delivery design into your development flow. Please see [Structure](https://github.com/Eve-PHP/Framework/blob/master/docs/Structure.md) for more information about this matter.