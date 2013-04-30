requests
========

Simple php cUrl wrapper.

```php

$post = Requests::post('http://localhost/index2.php', ['firstName' => 'Aleksandar', 'lastName' => 'Stevic']);

if ($post->statusCode() === 200) {
    echo $post->text();
}


$get = Requests::get('http://localhost/index2.php', ['firstName' => 'Aleksandar', 'lastName' => 'Stevic']);

if ($get->statusCode() === 200) {
    echo $get->text();
}


$auth = Requests::auth('http://localhost/index2.php', ['pelish8' => 'password']);

// send get request
$auth->get(['param1' => 'val1']);
// or
// send post request
// $auth->post(['param1' => 'val1']);
if ($auth->statusCode() === 200) {
    echo $auth->text();
}


$init = Requests::init('http://localhost/index2.php');

$init->setHeaders(['Content-Type: application/json', 'my-custom-header: my-custom-value']);

$init->setCookies(['cookie1=value1; ', 'cookie2=value2;', 'cookie3=value3']);

// send get request
// $init->get(['param1' => 'val1']);
// or
// send post request
$init->post(['param1' => 'val1']);

var_dump($init->headers()); // array of all headers

echo $init->headers('Content-Type'); // header with name provided as parameter or null if header with that name does not exist

var_dump($init->cookies()); // array of all cookies

echo $init->cookies('cookie_name'); // cookie with name provided as parameter or null if cookie with that name does not exist

if ($init->hasError()) {
    $error = $init->error();

    echo $error['number'] . ': ' . $error['message'];
} else {
    echo $init->text();
}







```
