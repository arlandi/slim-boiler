<?php

namespace SlimBoiler;

class Cache extends \Slim\Middleware
{

    /**
    * Location to save cached web pages
    *
    * @var string $path
    */
    public $path = './cache';

    /**
    * Cache lifetime
    *
    * @var int $duration duration in hours
    */
    public $duration = null;

    /**
    * Constructor
    *
    * @param array $config Config array
    * Eg:- $config = array('path' => './tmp', 'duration' => 1);
    */
    public function __construct( $config=array() )
    {
        if( isset($config['path']) ) {
            $this->path = $config['path'];
        }
        // Throw exception if cache directory is not writable/does not exist.
        if (!is_writable($this->path)) {
            throw new CacheException("Cache directory is not writable", 1);
        }
        if(isset($config['duration'])){
            $this->duration = $config['duration'];
        }
    }

    /**
    * SLim middleware call()
    *
    */
    public function call()
    {
        $app = $this->app;
        $path =  $app->request->getPath();
        $key = $this->clean(md5($path));
        if ($this->isCached( $key )) {
            // Cache hit
            $app->response->setBody($this->get( $key ));
            return;
        }
        else {
            // Cache miss, proceed to next middleware
            $this->next->call();
            $body = $app->response->getBody();
            $this->set($key, $body);
        }
        return;
    }

    /**
    * Cache web page using the given key
    *
    * @param string $key Cache file name
    * @param string $value Content
    */
    public function set($key, $value)
    {
        return file_put_contents($this->path .'/' . $key, $value);
    }

    /**
    * Get cached page if exists, or return false
    *
    * @param string $key Cache key
    */
    public function get($key)
    {
        if (!$this->isCached($key)) {
            return false;
        }
        return file_get_contents($this->path . '/' . $key);
    }

    /**
    * Check if the given key is cached
    *
    * @param string $key Key to be checked
    */
    public function isCached($key)
    {
        if (!is_readable($this->path . '/' . $key)) {
            return false;
        }
        if(!is_null($this->duration)) {
            $now = time();
            $expires = $this->duration * 60 * 60;
            $fileTime = filemtime($this->path . '/' . $key);
            if (($now - $expires) < $fileTime) {
                return false;
            }
        }
        return true;
    }

    /**
    * Clean string to make safe file names
    *
    * @param string $filename File name to be cleaned
    */
    function clean($filename)
    {
        return preg_replace('/[^0-9a-z\.\_\-]/i','', strtolower($filename));
    }
}

class CacheException extends \Exception{}
