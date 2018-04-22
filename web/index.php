<?php

use Symfony\Component\Debug\Debug;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;

require __DIR__.'/../vendor/autoload.php';
// The check is to ensure we don't use .env in production

if (!getenv('APP_ENV')) {
    (new Dotenv())->load(__DIR__.'/../.env');
}

if (getenv('APP_DEBUG')) {
    // WARNING: You should setup permissions the proper way!
    // REMOVE the following PHP line and read
    // https://symfony.com/doc/current/book/installation.html#checking-symfony-application-configuration-and-setup
    umask(0000);
    Debug::enable();
}

Request::setTrustedProxies(['0.0.0.0/0'], Request::HEADER_FORWARDED);
$kernel = new AppKernel(getenv('APP_ENV'), getenv('APP_DEBUG'));

$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
