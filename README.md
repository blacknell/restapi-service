# restapi-server - A simple class to expose a REST api

restapi-service maps REST API calls to endpoints in protected methods in your derived class.
In your class methods process according to the verbs and arguments of the http request.

## Installation

Install the latest version with
```
$ composer require blacknell/restapi-service
```

## Basic Usage

* Copy `example/api.php` and derive a class such as in `examples/MyAPI.class.php` into your 
web server directory
* Configure a `.htaccess` file to rewrite your RESTful call to your class

## Web Server configuration

For example, `https://yourserver/myapi/v1/daylight/littlehampton/yesterday` maps to
 `https://yourserver/myapi/v1/api.php?request=daylight/littlehampton/yesterday`
```
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule myapi/v1/(.*)$ myapi/v1/api.php?request=$1 [QSA,NC,L]
</IfModule>
```

## Sample code
See [example/MyAPI.class.php](https://github.com/blacknell/restapi-service/blob/master/example/MyAPI.class.php)
to see how `https://yourserver/myapi/v1/daylight/littlehampton/yesterday` generates the following JSON output
```
{
    "description": "Between sunrise and sunset yesterday",
    "sunrise": {
        "date": "2019-01-07 08:00:56.000000",
        "timezone_type": 3,
        "timezone": "Europe\/London"
    },
    "sunset": {
        "date": "2019-01-07 16:15:44.000000",
        "timezone_type": 3,
        "timezone": "Europe\/London"
    }
}
```
## Error Handling
Any endpoint not mapping to a protected function in your derived class results in a `RuntimeException`
being thrown and the following JSON response.
```
{
    "error": "No endpoint",
    "code": 404
}
```
Your derived class should do the same for invalid verbs or arguments.
Methods other than GET, POST, PUT or DELETE also result in an error.
## Logging
PSR-3 logging is supported via [monolog/monlog](https://github.com/Seldaek/monolog) by passing 
an optional `Logger` object to the API constructor.