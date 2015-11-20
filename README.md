![logo](http://eden.openovate.com/assets/images/cloud-social.png) 
Eve PHP Framework, build scalable apps for enterprise deploys.
====

<a name="install"></a>
## Install

1. `$ composer install eve-php/framework`
2. `$ vendor/bin/eve/install`
3. Follow the instructions.

====

<a name="features"></a>
## Features

 - Robust [routing](https://github.com/Eve-PHP/Framework/blob/master/docs/Middleware.md#routing)
 - [Event](https://github.com/Eve-PHP/Framework/blob/master/docs/Events.md) driven
 - Handlebars or PHP [templating](#templating)
 - Out of box [OAuth / REST](https://github.com/Eve-PHP/Framework/blob/master/docs/REST.md) actions
 - Pluggable with [middleware](https://github.com/Eve-PHP/Framework/blob/master/docs/Middleware.md) from [Packagist](https://packagist.org/packages/eve-php)
 - Code [Generator](#generators)
 - [Delayed](#job) Processes (Queuing)
 - Support for [Continuous Deployment](https://github.com/Eve-PHP/Framework/blob/master/docs/Tests.md)
 - [CLI](https://github.com/Eve-PHP/Framework/blob/master/docs/CLI.md) commands to integrate your app with other apps

====

<a name="started"></a>
## Getting Started

1. run `phpunit` to make sure everything works
2. try visiting `127.0.0.1` in your browser

If the browser does not load up. Add this on the bottom of your `httpd.conf` file.

```
#Allow Apache access to the public folder
<Directory "/[YOUR DIRECTORY]">
    Options Indexes FollowSymLinks MultiViews
    AllowOverride All
    Order allow,deny
    Allow from all
</Directory>

<VirtualHost *:80>
    DocumentRoot "/[YOUR DIRECTORY]"
    ServerName dummy-host.example.com
</VirtualHost>
```

Then restart apache by either `$ sudo service httpd restart` or `$ sudo apachectl restart`.

If you would like to not use the IP address you may do so by

```
<VirtualHost custom.com:80>
    DocumentRoot "/[YOUR DIRECTORY]"
    ServerName dummy-host.example.com
</VirtualHost>
```

and then in `/etc/hosts` add in a new line

```
127.0.0.1 custom.com
```

Once you see `You have successfully installed this framework!` in your browser you are in good shape :) . Login to `127.0.0.1/control/login` using `admin@openovate.com` password `admin` to verify the rest of the framework is working.

====

<a name="structure"></a>
## Structure

![Request Response](https://github.com/Eve-PHP/Framework/blob/master/docs/rnr.jpg)

Eve though heavily adopts some parts, is not a typical MVC pattern. As well as MVC, Eve adopts jobs, events, continuous deploys and REST in the normal architecture to help optimize PHP and organize your code overall. The following structure should reflect the folders of what is installed.

 - `App` - Where all your actions and views go
     - `Back` - Default location for admin specific pages
     - `Dialog` - Default location for UI based API calls
     - `Front` - Default for publicly accessable pages
     - `Rest` - Default location for REST based API calls
 - `Job` - Used to identify business rules by combining several model calls 
 - `Model` - Unit style CRUD to access the database
 - `public` - static files and DMZ
 - `settings` - App specific configuration
 - `test` - Used for Continuous Integration

To read more about the structure please refer to [Structure](https://github.com/Eve-PHP/Framework/blob/master/docs/Structure.md).

<a name="app"></a>
### App Layer

The purpose of the Application Layer is to abstract the responsibility of rendering the page only in this folder. Out of the box, Eve comes with 4 applications to get you started faster `Back`, `Dialog`, `Front`, and `Rest`. These apps are activated in `public/index.php`. So to deactivate specific apps you may do so by removing the respective lines and proceed to remove the app folder. For example to remove the Dialog App, remove the following from `public/index.php`.

```
//Dialog Route
->add(Eve\App\Dialog\Route::i()->import())
```

Then `$ rm -rf App/Dialog`. Like wise, the easiest way to add a custom app is to copy one of the app folders and that that to `public/index.php`. For example to add a mobile version of your app, add the following to `public/index.php` before `->defaultBootstrap()`.

```
//Mobile Route
->add(Eve\App\Mobile\Route::i()->import())
```

Then `cp -rf App/Front App/Mobile`. Last Search and replace `Front` with `Mobile`. Be sure you update your routes in `App/Mobile/routes.php`

Every file inside the `App` folder is designed to be a template example, so feel free to modify anything to your liking. Inside every App you will find `Route.php` and `routes.php`. Apps in the Eve framework are treated as middleware as defined in [Eden Server](https://github.com/Eden-PHP/Server/blob/master/README.md) so read up about middleware before continuing. 

External Plugins are also middleware found in Packagist and can be imported like Apps. The following are example plugins you can use out of the box with Eve.

 - [i10n](https://packagist.org/packages/eve-php/eve-plugin-l10n)
 - [HTPASSWD](https://packagist.org/packages/eve-php/eve-plugin-htpasswd)
 - [CSRF Checker](https://packagist.org/packages/eve-php/eve-plugin-csrf)
 - [Google Captcha](https://packagist.org/packages/eve-php/eve-plugin-captcha)

All middleware should return a function for the framework to process when all middleware has been imported. This is the purpose for `import()` inside of every `Route.php` file. The second purpose is to register routes as defined by `routes.php` where a route is assigned to an Action Class.

Action classes only require to have a `render()` function in which the request object can be accessed by `$this->request` and the response object is accessed by `$this->response`. Both objects follow the same standards defined by [Eden Registry](https://github.com/Eden-PHP/Registry/blob/master/README.md). The `render()`. If you desire an output to be rendered in the browser, you may do so by simply returning the string inside of `render()`. The following example explains how this can be done.

Create a file called `Sample.php` inside the `App/Front/Action` folder. Then paste the following code.

```
namespace Eve\App\Front\Action;

use Eve\Framework\Action\Html;

class Sample extends Html
{
    /**
     * Main action call
     *
     * @return string|null|void
     */
    public function render()
    {
        return 'Hello World!';
    }
}
```

Then in `routes.php` add the following.

```
    '/foo' => array(
        'method' => 'ALL',
        'class' => '\\Eve\\App\\Front\\Action\\Sample'
    ),
```

Visit `127.0.0.1/foo` in your browser. By default if you want to use a template you may do so by matching the path of the action file. For example, if you create a `App/Front/Foo/Bar` class, the respective template should be found at `App/Front/foo/bar.html`. Modifying the above example, we can use a template instead of returning a raw string with the following process.

Open the file called `Sample.php` inside the `App/Front/Action` folder. Then replace the existing code with the following code.

```
namespace Eve\App\Front\Action;

use Eve\Framework\Action\Html;

class Sample extends Html
{
    /**
     * Main action call
     *
     * @return string|null|void
     */
    public function render()
    {
        return $this->success();
    }
}
```

<a name="templating"></a>
Create a file called `sample.html` inside the `App/Front/template` folder. Then paste the following code.

```
Hello World 2!
```

Visit `127.0.0.1/foo` in your browser. By default templates in Eve use handlebars. This choice was made to easily integrate single page apps and phonegap by having a common templating standard. Though it is possible to use PHP templating by renaming `sample.html` to `sample.php` or `sample.phtml` it is not recommended because of the fore mentioned. PHP variables can be accessed in the template with the following example.

Open the file called `Sample.php` inside the `App/Front/Action` folder. Then replace the existing code with the following code.

```
namespace Eve\App\Front\Action;

use Eve\Framework\Action\Html;

class Sample extends Html
{
    /**
     * Main action call
     *
     * @return string|null|void
     */
    public function render()
    {
		$this->body['foo'] = 'bar';
        return $this->success();
    }
}
```

Open the file called `sample.html` inside the `App/Front/template` folder. Then replace the existing code with the following code.

```
Hello {{foo}} !
```

Visit `127.0.0.1/foo` in your browser. To know more about Handlebars templating visit the [Handlebars Website](http://www.handlebarsjs.com).

<a name="job"></a>
### Job Layer

The purpose of the Job Layer is to abstract the responsibility of business rules only in this folder. Specifically, jobs are introduced in Eve for the following purposes.

 - Grouping of business requirements
 - Reusable code for writable actions
 - Anti corruption of the model layer
 - Delaying PHP process thereby speeding the response time

Action classes should consider calling jobs rather than the models to write to the database to promote re-usability. Based examples of jobs are creating an object, updating an object and removing an object. Some advanced example of jobs could be,

 - Uploading a product list
 - Sending an email
 - Sending an SMS

Since retrieving a list or object detail normally requires an immediate response (or read actions) it's okay to call models in actions.

Jobs can be called in the CLI using the following example command.

```
vendor/bin/eve job send-mail "?subject=hello&body=world"
```

or can be called in code,

```
eve()
	->job('mail-send')
	->setData(array('subject' => 'hello', 'body' => 'world'))
	->run();
```

Eve also comes with an interface to connect to [RabbitMQ](http://rabbitmq.com/). To queue up a job you can either use the following CLI command,

```
vendor/bin/eve queue send-mail "?subject=hello&body=world"
```

or in code,

```
eve()
	->queue('mail-send')
	->setData(array('subject' => 'hello', 'body' => 'world'))
	->run();
```

An example business rule like creating a product could entail,

 1. Insert product row to database
 2. Linking profile with product
 3. Insert product file/s to database
 4. Linking file with product
 
To illustrate this idea, we can accomplish this by creating a job with the following code,

```
namespace Eve\Job\Product;

use Eve\Framework\Job\Base;
use Eve\Framework\Job\Exception;

class Create extends Base
{
    const FAIL_406 = 'Invalid Data';

    public function run()
    {
        //if no data
        if (empty($this->data)) {
            //there should be a global catch somewhere
            throw new Exception(self::FAIL_406);
        }

        //this will be returned at the end
        $results = array();

        //NEXT ...

        //if there is no product_id provided
        if (!isset($this->data['product_id'])) {
            //create the product
            $results['product'] = eve()
                ->model('product')
                ->create()
                ->process($this->data)
                ->get();

            $this->data['product_id'] = $results['product']['product_id'];
        }

        //NEXT ...

        //if there is a profile_id
        if (isset($this->data['profile_id'])) {
            //link the profile
            eve()
                ->model('product')
                ->linkProfile(
                    $results['product']['product_id'],
                    $this->data['profile_id']
                );
        }

        //NEXT ...

        //if there is a list of file
        if (isset($this->data['file'])
            && is_array($this->data['file'])
        ) {
            foreach($this->data['file'] as $i => $row) {                    
				//create the file
				$row = eve()
					->model('file')
					->create()
					->process($row)
					->get();
                
                //now link the files
                eve()
                    ->model('product')
                    ->linkFile(
                        $results['product']['product_id'],
                        $row['file_id']
					);
				
				//build thumb
				eve()
					->job('random-resize')
					->setData($row)
					->run();
            }
        }
		
        return $results;
    }
}
```

To read more about Jobs please read [Jobs and Delayed Process](https://github.com/Eve-PHP/Framework/blob/master/docs/Jobs.md).

<a name="model"></a>
### Model Layer

The purpose of the Model Layer is to abstract the responsibility of accessing the database only in this folder. Unlike most model layers in practical MVC structures, Eve's model layer has a unique separation of CRUD and generic usage. The following command is used to access a model.

```
eve()->model('auth');
```

The class that is returned is merely a factory class with other possible random database accessors. The factory methods for the `auth` model particularly forwards to different CRUD classes. For example to access the auth CRUD you may do so with the following code.

```
//create
eve()->model('auth')->create();

//update
eve()->model('auth')->update();

//remove
eve()->model('auth')->remove();

//search
eve()->model('auth')->search();

//detail
eve()->model('auth')->detail();
```

Each of the CRUD classes follow a similar pattern. All CRUD classes should have a method called `error()` and a method called `process()`. For example to create a new auth row you can do so given the following example.

```
eve()
	->model('auth')
	->create()
	->process(array(
		'auth_slug' 		=> 'sample@email.com',
		'auth_permissions' 	=> 'test_permissions_1,test_permissions_2',
		'auth_password'    	=> '123456',
		'confirm' 			=> '123456'));
```

It is good practice however to check for errors before processing. You can do so with the following example.

```
eve()
	->model('auth')
	->create()
	->errors(array(
		'auth_slug' 		=> 'sample@email.com',
		'auth_permissions' 	=> 'test_permissions_1,test_permissions_2',
		'auth_password'    	=> '123456',
		'confirm' 			=> '123456'));
```

All other methods that logically cannot follow this pattern can be put into `Model/Auth/Index.php`. For example the following code checks to see if the auth_id 1 exists.

```
eve()->model('auth')->exists(1); //--> true or false
```

====

<a name="generators"></a>
## Generators

Eve comes with a code generator which is only recommended to use if you understand how the underlying structure, classes and coding standards included in this framework. Example schemas can be found in the `schema` folder. To get an overview of the capabilities of the generator you may do so with the following command.

```
$ vendor/bin/eve generate sink
```

This command will generate the kitchen sink code as defined by `schema/sink.php`. To also add this schema to your database you may do so with the following command.

```
$ vendor/bin/eve database sink
```

To see the sink UI add the following to App/Back/routes.php

```
    '/control/sink/search' => array(
        'method' => 'GET',
        'class' => '\\Eve\\App\\Back\\Action\\Sink\\Search'
    ),
    '/control/sink/create' => array(
        'method' => 'ALL',
        'class' => '\\Eve\\App\\Back\\Action\\Sink\\Create'
    ),
    '/control/sink/update' => array(
        'method' => 'ALL',
        'class' => '\\Eve\\App\\Back\\Action\\Sink\\Update'
    ),
    '/control/sink/remove' => array(
        'method' => 'GET',
        'class' => '\\Eve\\App\\Back\\Action\\Sink\\Remove'
    ),
    '/control/sink/restore' => array(
        'method' => 'GET',
        'class' => '\\Eve\\App\\Back\\Action\\Sink\\Restore'
    ),
```

Then visit `127.0.0.1/control/sink/search` to see the kitchen sink generated visually from the schema.