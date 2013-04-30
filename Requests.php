<?php

namespace pelish8\Requests;

/**
 * Request
 *
 * @package Requests
 * @author  pelish8
 * @since   0.1
 */
class Requests
{

    protected function __construct()
    {
    }

    /**
     * init request
     *
     */
    public static function init($url, array $auth = [])
    {
        return new \pelish8\Requests\Request($url, $auth);
    }

    /**
     * init user and set authorization params
     *
     */
    public static function auth($url, array $auth)
    {
        $request = static::init($url);
        return $request->auth($auth);
    }

    /**
     * do get request
     *
     */
    public static function get($url, array $params)
    {

        $request = static::init($url);
        return $request->get($params);
    }

    /**
     * do post request
     *
     */
    public static function post($url, array $params)
    {

        $request = static::init($url);
        return $request->post($params);
    }
}
