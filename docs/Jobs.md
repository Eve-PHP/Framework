# Jobs and Delayed Process

In a typical MVC design, controllers determine the action, actions would have access to models, while models have access to the database. This kind of separation allows models to be reused for different apps. In practice however, because of the nature of changing business requirements, it is possible for model methods to be app specific which was not the intention of the original MVC design. 

Those who successfully kept the intention of models, stuffed business rules in the action classes, but the problem with this is other apps could not reuse it because actions are normally binded to very specific templates. This was one of the main reasons why we introduced a new layer for business rules called `Jobs`. Jobs are reusable components across many apps to access different kind of business rules which inturn accesses models to interact to the database. Jobs can be called in the CLI using the following example command.

```
$ vendor/bin/eve job send-mail "?subject=hello&body=world"
```

or can be called in code,

```
eve()
	->job('mail-send')
	->setData(array('subject' => 'hello', 'body' => 'world'))
	->run();
```

The second reason for Jobs is to address an underlying problem with PHP in general. While other languages embrace asyncronous design, PHP has been lacking in this intervention. Because of this, PHP benchmarks in comparison to other languages are regrettably slower. the reason why Jobs helps solves this is because we can now incorporate a job queue into the architecture for things that can be delayed. Examples of delayed jobs are the following.

 - Grouping of business requirements
 - Reusable code for writable actions
 - Anti corruption of the model layer
 - Delaying PHP process thereby speeding the response time

Eve provides an interface using *RabbitMQ* out of the box for Delayed Jobs which looks like the following.

```
eve()->queue(
	'mail-send', 
	array(
		'body' => 'Hello',
		'subject' => 'Hi',
		'to' => array('example@sample.com')
	))
	->setPriority('high')
	->setDelay(30);
```

If you have *RabbitMQ* installed you can run `php worker.php` to run the Job Queue. There are many options to `queue()`, 2 of these in the example above can be setting a priority to a job with `setPriority()` and delaying a job with `setDelay()`. To see what else there is you can take a look at [the queue class](https://github.com/Eve-PHP/Framework/blob/master/src/Queue.php). Like Jobs described earlier there is also a CLI command to queue jobs. The following illustrates this.


```
$ vendor/bin/eve queue send-mail "?subject=hello&body=world" high 30
```

On top of Jobs, Eve now has an event handler for post processing; after the response has been sent back to the user called `shutdown`. An implementation would look like the following implemented in an action.

```
eve()->on('shutdown', function() {
	//PROCESS THIS AFTER RESPONSE
});
```

