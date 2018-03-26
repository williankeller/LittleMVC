<?php

/**
 * Stores data within Memcache.
 *
 * Copyright (C) 2018 MVC Framework.
 * This file included in MVC Framework is licensed under OSL 3.0
 *
 * http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Please see LICENSE.txt for the full text of the OSL 3.0 license
 */

namespace Application\Core\Storage;

class Memcache
{

    /**
     * The reference to the Memcache server.
     *
     * @access private
     * @var    \Memcache or \Memcached
     */
    private $_memcache;

    /**
     * Setup Memcache for storing data.
     *
     * @access public
     * @param  string     $server Whether we are using Memcache or Memcached.
     * @param  string     $host   The location of the Memcache server.
     * @param  string     $port   The port the Memcache server lives on.
     * @throws \Exception         If passed an incorrect server.
     */
    public function setup($server, $host, $port)
    {
        // Sanity check: Make sure we have received a valid
        switch ($server) {
            case 'Memcache' : $this->_memcache = new \Memcache();
                break;
            case 'Memcached' : $this->_memcache = new \Memcached();
                break;
            default : throw new \Exception("Unknown server {$server}.");
        }

        // Memcache instance created, add the server
        $this->_memcache->addServer($host, $port);
    }

    /**
     * Check whether the variable exists in the store.
     *
     * @access public
     * @param  string  $variable The name of the variable to check existence of.
     * @return boolean           If the variable exists or not.
     */
    public function has($variable)
    {
        return (bool) $this->_memcache->get($variable);
    }

    /**
     * Store a variable for use.
     *
     * @access public
     * @param  string  $variable  The name of the variable to store.
     * @param  mixed   $value     The data we wish to store.
     * @param  boolean $overwrite Whether we are allowed to overwrite the variable.
     * @return boolean            If we managed to store the variable.
     * @throws Exception          If the variable already exists when we try not to overwrite it.
     */
    public function put($variable, $value, $overwrite = false)
    {
        // If it exists, and we do not want to overwrite, then throw exception
        if ($this->has($variable) && !$overwrite) {
            throw new \Exception("{$variable} already exists in the store.");
        }

        return $this->_memcache->set($variable, $value);
    }

    /**
     * Return the variable's value from the store.
     *
     * @access public
     * @param  string $variable The name of the variable in the store.
     * @return mixed
     * @throws Exception        If the variable does not exist.
     */
    public function get($variable)
    {
        if (!$this->has($variable)) {
            throw new \Exception("{$variable} does not exist in the store.");
        }

        return (bool) $this->_memcache->get($variable);
    }

    /**
     * Remove the variable in the store.
     *
     * @access public
     * @param  string $variable The name of the variable to remove.
     * @return boolean          If the variable was removed successfully.
     * @throws Exception        If the variable does not exist.
     */
    public function remove($variable)
    {
        if (!$this->has($variable)) {
            throw new \Exception("{$variable} does not exist in the store.");
        }

        return (bool) $this->_memcache->delete($variable);
    }

}
