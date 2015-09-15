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

class Compress {

  public function addStyleSheet($data = array()) {

    $this->type = 'css';

    return $this->checkData($data);
  }

  public function addJavaScript($data = array()) {

    $this->type = 'js';

    return $this->checkData($data);
  }

  protected function checkData($data) {

    if (isset($data['ensemble'])) {

      return $this->filesEnsemble($data);
    } else if (isset($data['inherent'])) {

      return $this->filesInherent($data);
    } else {
      return $this->filesDefalt($data);
    }
  }

  private function filesEnsemble($data) {

    return $this->fileCallOut($this->fileWrite($data));
  }

  private function filesInherent($data) {

    $content = $this->arrayContent($data['includes']);

    if (isset($data['compress'])) {

      $content = $this->filesCompress($content);
    }

    return $this->fileCallOut($content, true);
  }

  private function filesDefalt($data) {

    foreach ($data['includes'] as $file) {

      $content[] = $this->fileCallOut($this->_folder($file, true));
    }

    return implode(PHP_EOL, $content);
  }

  private function filesCompress($data) {

    return $this->compressContent($data);
  }

  private function fileCallOut($content, $method = null) {

    if ($method === true) {

      switch ($this->type) {

        case 'css':
          return '<style>' . $content . '</style>';
        case 'js':
          return '<script>' . $content . '</script>';
        default :
          return $content;
      }
    }
    switch ($this->type) {

      case 'css':
        return '<link rel="stylesheet" type="text/css" href="' . $content . '"/>';
      case 'js':
        return '<script src="' . $content . '"></script>';
      default :
        return $content;
    }
  }

  /*
   * Function read file
   * @param varchar $file
   * @returns varchar $file content
   */

  private function fileRead($file) {

    /*
     * Open file content
     */
    $fopen = fopen($this->_folder($file), 'r');

    return fread($fopen, filesize($this->_folder($file)));
  }

  private function fileWrite($data) {

    $content = $this->arrayContent($data['includes']);

    $filename = 'assets/' . $this->type . '/min/' . md5(date('Y-m')) . '.' . $this->type;

    $myfile = fopen($this->_folder($filename), "w") or die("Unable to open file!");

    if (isset($data['compress'])) {

      fwrite($myfile, $this->filesCompress($content));
    } else {

      fwrite($myfile, $content);
    }
    fclose($myfile);

    return $this->_folder($filename, true);
  }

  private function arrayContent($files) {

    foreach ($files as $file) {

      $content[] = $this->fileRead($file);
    }

    return implode(PHP_EOL, $content);
  }

  private function _folder($arquivo, $method = null) {

    // Retorna a rota base do arquivo com a extenção
    return (($method != null) ? URL : APP_PATH) . $arquivo;
  }

  /**
   * _compressContent()
   * Função responsável por remover os espaços desnecessários entre os caracteres
   *
   * @function private
   * @return void
   */
  private function compressContent($content) {

    $this->content = $content;

    $this->content = $this->_removeComments($this->content);

    $this->content = $this->_removeAllSpaces($this->content);

    $this->content = $this->_removeCharSpaces($this->content);

    return $this->content;
  }

  private function _removeCharSpaces($content) {

    $this->content = $content;

    $this->content = str_replace(array(" {", "{ "), '{', $this->content);
    $this->content = str_replace(array(" }", "} "), '}', $this->content);
    $this->content = str_replace(array(' <', '< '), '<', $this->content);
    $this->content = str_replace(array(' >', '> '), '>', $this->content);
    $this->content = str_replace(array(' +', '+ '), '+', $this->content);
    $this->content = str_replace(array(' -', '- '), '-', $this->content);
    $this->content = str_replace(array(' ]', '] '), ']', $this->content);
    $this->content = str_replace(array(' [', '[ '), '[', $this->content);
    $this->content = str_replace(array(';}', '} '), '}', $this->content);
    $this->content = str_replace(array(' ;', '; '), ';', $this->content);
    $this->content = str_replace(array(' (', '( '), '(', $this->content);
    $this->content = str_replace(array(' )', ') '), ')', $this->content);
    $this->content = str_replace(array(' ,', ', '), ',', $this->content);
    $this->content = str_replace(array(' :', ': '), ':', $this->content);

    return $this->content;
  }

  private function _removeAllSpaces($content) {

    $this->content = $content;

    $this->content = str_replace(array("\t", "\n", "\r", '  ', '    ', '     '), '', $this->content);
    $this->content = str_replace(array(' =', '= '), '=', $this->content);
    $this->content = str_replace(array(' ==', '== '), '==', $this->content);
    $this->content = str_replace(array(' &&', '&& '), '&&', $this->content);
    $this->content = str_replace(array(' ||', '|| '), '||', $this->content);
    $this->content = str_replace(array(' !==', '!== '), '!==', $this->content);
    $this->content = str_replace(array(' ===', '=== '), '===', $this->content);

    return $this->content;
  }

  private function _removeComments($content) {

    $this->content = $content;

    $this->content = preg_replace('!/\*.*?\*/!s', '', $this->content);
    $this->content = preg_replace('/\n\s*\n/', "\n", $this->content);
    $this->content = preg_replace('/((?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:\/\/.*))/', '', $this->content);

    return $this->content;
  }

}
