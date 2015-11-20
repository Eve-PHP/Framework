# Middleware

Eve is an Express style web service that heavily relies on external middleware. A quick example of this usage is found below.

```
eve()
	->route('*', function($request, $response) {
		$response->set('body', 'Hello World!');
	})
	->render();
```

There are 3 kinds of middleware it accepts and are called during specific times during the response process.

### Global Middleware

Global Middleware are called before any response is generated. Some examples of global middleware can be

 - Security - like CSRF checking, Captcha, CORS, HTPASSWD, etc.
 - API - like Facebook Login, Paypal, etc.
 - Utility - like geoip, localization, internationalization, etc.

The following are example plugins you can use out of the box with Eve.

 - [i10n](https://packagist.org/packages/eve-php/eve-plugin-l10n)
 - [HTPASSWD](https://packagist.org/packages/eve-php/eve-plugin-htpasswd)
 - [CSRF Checker](https://packagist.org/packages/eve-php/eve-plugin-csrf)
 - [Google Captcha](https://packagist.org/packages/eve-php/eve-plugin-captcha)
 
You can simply add global middleware in `public/index.php` using this fashion.

```
eve()->add(function($request, $response) {
	$response->set('body', 'Hello World!');
});
```

<a href="routing"></a>
### Route Middleware

Route Middleware are called when the request is formed right after the Global Middleware. To make a route available you will need the request method, desired route path and the callback handler.

You can simply add route middleware in `public/index.php` using this fashion.

```
eve()->route('POST', '/some/path/*/foo', function($request, $response) {
	$response->set('body', 'Hello World!');
});
```

Routes can accept dynamic variables denoted as `*`, described in the example route `/some/path/*/foo`. These variables are accessable by calling `$id = $request->get('variables', 0);` in your route handler callback. If your route is using a common request method like `POST`, `GET`, `PUT`, `DELETE`, there are wrapper methods recommended to use instead.


```
eve()->post('/some/path/*/foo', function($request, $response) {
	$response->set('body', 'Hello World!');
});
```

```
eve()->get('/some/path/*/foo', function($request, $response) {
	$response->set('body', 'Hello World!');
});
```

```
eve()->put('/some/path/*/foo', function($request, $response) {
	$response->set('body', 'Hello World!');
});
```

```
eve()->delete('/some/path/*/foo', function($request, $response) {
	$response->set('body', 'Hello World!');
});
```

For all the above methods you can also set the response by returning the string like below.

```
eve()->get('/some/path/*/foo', function($request, $response) {
	return 'Hello World';
});
```

### Error Middleware

Error Middleware are called when either the Global or the Route Middleware throws an Exception. You can simply add an error middleware in this fashion.

```
eve()->error(function(
		$request, 
		$response, 
		$type,
		$level,
		$class,
		$file,
		$line,
		$message
	) {
		$response->set('body', 'Hello World!');
	});
```

====

<a name="api"></a>
## API

==== 

<a name="add"></a>

### add

Adds global middleware 

#### Usage

```
eve()->add(function $callback);
```

#### Parameters

 - `function $callback` - The middleware handler

Returns `Eve\Framework\Index`

#### Example

```
eve()->add(function($request, $response) {
	$response->set('body', 'Hello World!');
});
```

==== 

<a name="all"></a>

### all

Adds routing middleware for all methods 

#### Usage

```
eve()->all(string $path, function $callback);
```

#### Parameters

 - `string $path` - The route path
 - `function $callback` - The middleware handler

Returns `Eve\Framework\Index`

#### Example

```
eve()->all('/some/*/path', function($request, $response) {
	$response->set('body', 'Hello World!');
});
```

==== 

<a name="delete"></a>

### delete

Adds routing middleware for delete method 

#### Usage

```
eve()->delete(string $path, function $callback);
```

#### Parameters

 - `string $path` - The route path
 - `function $callback` - The middleware handler

Returns `Eve\Framework\Index`

#### Example

```
eve()->delete('/some/*/path', function($request, $response) {
	$response->set('body', 'Hello World!');
});
```

==== 

<a name="error"></a>

### error

Adds error middleware 

#### Usage

```
eve()->error(function $callback);
```

#### Parameters

 - `function $callback` - The middleware handler

Returns `Eve\Framework\Index`

#### Example

```
eve()->error('/some/*/path', function($request, $response) {
	$response->set('body', 'Hello World!');
});
```

==== 

<a name="get"></a>

### get

Adds routing middleware for get method 

#### Usage

```
eve()->get(string $path, function $callback);
```

#### Parameters

 - `string $path` - The route path
 - `function $callback` - The middleware handler

Returns `Eve\Framework\Index`

#### Example

```
eve()->get('/some/*/path', function($request, $response) {
	$response->set('body', 'Hello World!');
});
```

==== 

<a name="post"></a>

### post

Adds routing middleware for post method 

#### Usage

```
eve()->post(string $path, function $callback);
```

#### Parameters

 - `string $path` - The route path
 - `function $callback` - The middleware handler

Returns `Eve\Framework\Index`

#### Example

```
eve()->post('/some/*/path', function($request, $response) {
	$response->set('body', 'Hello World!');
});
```

==== 

<a name="put"></a>

### put

Adds routing middleware for put method 

#### Usage

```
eve()->put(string $path, function $callback);
```

#### Parameters

 - `string $path` - The route path
 - `function $callback` - The middleware handler

Returns `Eve\Framework\Index`

#### Example

```
eve()->put('/some/*/path', function($request, $response) {
	$response->set('body', 'Hello World!');
});
```

==== 

<a name="route"></a>

### route

Adds routing middleware 

#### Usage

```
eve()->route(string $method, string $path, function $callback);
```

#### Parameters

 - `string $method` - The request method
 - `string $path` - The route path
 - `function $callback` - The middleware handler

Returns `Eve\Framework\Index`

#### Example

```
eve()->route('POST', '/some/*/path', function($request, $response) {
	$response->set('body', 'Hello World!');
});
```