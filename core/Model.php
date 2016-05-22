<?php

/*
 * Copyright (C) 2015 wkeller
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

class Model {
  /*
   * Function to load model
   * @param varchar $model
   * @return object (Class name)
   */

  public function load($model = false) {

    // See if exists model passed in function param
    if (!$model) {

      // Case not, get current controller as Model
      $model = Router::getController() . 'Model';
    }

    // Change the model name first letter to upercase
    $name = ucfirst($model);

    // File path
    $path = SYS_PATH . 'models' . DIRECTORY_SEPARATOR . $name . '.php';

    // Verify if file exists
    if (file_exists($path)) {

      // Include the file
      require_once $path;

      /* @var $name type */
      if (class_exists($name)) {

        // Return class object
        return new $name();
      }
      return;
    } else {
      throw new Exception(sprintf("Called model on this page not be found Model Name: [%s]", $name));
    }
  }
  
  /**
   * Function to get define cookie
   * @param varchar $name
   * @return boolean
   */
  public function getCookie($name) {
    
    if (isset($_COOKIE[$name])) {
      
      // Remove adition slashes
      $content = stripslashes($_COOKIE[$name]);
      
      // Verify id serilialized content
      if (self::isSerialized($content)) {
        return unserialize($content);
      }
      return $content;
    }
    return false;
  }
  
  /**
   * Function to set new cookie 
   * @param char $name
   * @param char $content
   * @param boolean $serialize
   * @param char $route
   */
  public function setCookie($name, $content, $serialize = true, $time = (86400 * 30), $route = "/") {

    if ($serialize) {
      $content = serialize($content);
    }
    // Se cookie action
    setcookie($name, $content, time() + $time, $route);
  }
  
  /**
   * Function to verify serialized content
   * @param char $str
   * @return boolean
   */
  public function isSerialized($str) {

    return ($str == serialize(false) || @unserialize($str) !== false);
  }

  /*
   * Validate en clear up URL
   * @param char $url
   * @return rtrim url
   */
  public function clearURL($url) {

    if (filter_var($url, FILTER_VALIDATE_URL) === FALSE) {

      return false;
    }
    return rtrim($url, "/");
  }

  /*
   * Enconde json values
   * @param {varchar} $str
   * @returns json
   */
  public function jsonEncode($str) {

    $a = array('"{\"', '\":\"');
    $b = array('{"', '":"');
    $c = json_encode($str);
    $d = str_replace($a, $b, $c);

    return $d;
  }

  /*
   * Verify if ajax request
   */
  final public function IsAJAX() {

    return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'));
  }

}
