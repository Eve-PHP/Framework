# Events

Eve emits several events during the request and response process for your perusal. Custom events can be triggered anywhere in your code by using the following example.

```
eve()->trigger('custom-event', $value1, $value2 ...);
```

The following describes the constant events triggered throughout the framework.

## Global Events

```
eve()->on('config', function() {
	//This happens when paths, database and PHP handlers are set
});
```

```
eve()->on('init', function() {
	//This happens after config and when timezone is set
});
```


```
eve()->on('session', function() {
	//This happens after init and when session is started
});
```

```
eve()->on('request', function() {
	//This happens after session when Request, Response and routes are defined
});
```

```
eve()->on('response', function() {
	//This happens right after a chosen route was processed but not rendered
});
```

```
eve()->on('response-success', function() {
	// This happens when the action returns $this->success();
});
```

```
eve()->on('response-fail', function() {
	// This happens when the action returns $this->fail();
});
```

```
eve()->on('render', function() {
	//This happens right after the output was rendered
});
```

```
eve()->on('shutdown', function() {
	//This happens after the response was given and the connection is closed
	//for post processing.
});
```

# Action Events

```
eve()->on('html-fail', function() {
	//This happens when the Action is an HTML Page and returns $this->fail()
});
```

```
eve()->on('html-success', function() {
	//This happens when the Action is an HTML Page and returns $this->success()
});
```

```
eve()->on('json-fail', function() {
	//This happens when the Action is a JSON Page and returns $this->fail()
});
```

```
eve()->on('json-success', function() {
	//This happens when the Action is a JSON Page and returns $this->success()
});
```

# Database Events

```
eve()->on('query', function() {
	//This happens whenever the database has been queried
});
```