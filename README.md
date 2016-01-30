# Copylancer API wrapper

To use it simply load this class. Set login and password and create new object.

Example of using methods with auth method (This method will be executed with constructor of class, this is just an example):

```php
$copylancer = new CopylancerBasic();
$copylancer->invokeMethod('Users', 'auth', 'GET', ['name' => 'myname', 'password' => 'mypassword']);
```