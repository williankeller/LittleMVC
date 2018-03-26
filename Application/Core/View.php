<?php

/**
 * The view class.
 * Responsible for rendering files as HTML, encode JSON, with some helper methods
 *
 * Copyright (C) 2018 MVC Framework.
 * http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Please see LICENSE.txt for the full text of the OSL 3.0 license
 */

namespace Application\Core;

class View
{

    /**
     * Build sections route.
     */
    const SECTIONS = '/View/Sections';

    /**
     * Build content route.
     */
    const CONTENTS = '/View/Contents';

    /**
     * Controller object that instantiated view object
     *
     * @var object
     */
    public $controller;

    /**
     * Parameters array that instantiated view page.
     *
     * @var array
     */
    public $head = [];

    /**
     * Parameters array that instantiated view page.
     *
     * @var array
     */
    public $params = [];

    /**
     * Constructor
     *
     * @param Controller $controller
     */
    public function __construct(
        Controller $controller
    ) {
        $this->controller = $controller;
    }

    /**
     * Renders and returns output for the given file with its array of data.
     *
     * @param  string  $filePath
     * @param  array   $data
     * @return string  Rendered output
     */
    public function content($filePath, $data = null)
    {
        if (!empty($data)) {
            extract($data);
        }

        // Using include Vs require is better,
        // because we may want to include a file more than once.
        ob_start();
        include $filePath . '';
        $renderedFile = ob_get_clean();

        $this->controller->response->setContent($renderedFile);
        return $renderedFile;
    }

    /**
     * Renders and returns output with header and footer for the given file with its array of data.
     *
     * @param  string  $layoutDir
     * @param  string  $filePath
     * @param  array   $data
     * @return string  Rendered output
     */
    public function render($data = null)
    {
        if (!empty($data)) {
            extract($data);
        }

        $rendered = $this->buildStrucure();

        $this->controller->response->setContent($rendered);
        return $rendered;
    }
    
    public function buildStrucure()
    {
        $sections = $this->loadSections();
        $structure = $this->loadLayout();
        
        $rendered = str_replace('{{content}}', $sections, $structure);
        
        return $rendered;
    }

    public function loadLayout($layout = 'default')
    {
        // Enable object buffering.
        ob_start();

        // And include the file for parsing.
        include sprintf('%s/View/Layout/%s.phtml', APPLICATION, $layout);

        // Get the content of the view after parsing, and dispose of the buffer.
        $renderedFile = ob_get_clean();
        return $renderedFile;
    }

    /**
     * Build sections.
     *
     * @return $this
     */
    public function loadSections()
    {
        $block = Handler::get('SECTIONS');
        $pages = Handler::get('CONTENTS');

        $params = $this->controller->request->params;
        $router = [
            $params['controller'],
            $params['action'],
        ];

        // Join to make a path.
        $get = implode('/', $router);

        $sections = [
            sprintf("%s/header%s", $block, '.phtml'),
            sprintf("%s/{$get}%s", $pages, '.phtml'),
            sprintf("%s/footer%s", $block, '.phtml'),
        ];
            
        ob_start();
        foreach ($sections as $section) {
            include $section;
        }
        $renderedFile = ob_get_clean();
        return $renderedFile;
    }

    /**
     * Safer and better access to $this->head.
     *
     * @param  string   $key
     * @return mixed
     */
    public function head($key)
    {
        if (!array_key_exists($key, $this->head)) {
            return null;
        }
        return $this->head[$key];
    }

    /**
     * Add parameters to $this->head.
     *
     * @param  array $params
     * @return Request
     */
    public function addHead(array $params)
    {
        $this->head = array_merge($this->head, $params);
        return $this;
    }

    /**
     * Safer and better access to $this->params
     *
     * @param  string   $key
     * @return mixed
     */
    public function param($key)
    {
        if (!array_key_exists($key, $this->params)) {
            return null;
        }
        return $this->params[$key];
    }

    /**
     * Add parameters to $this->params.
     *
     * @param  array $params
     * @return Request
     */
    public function addParams(array $params)
    {
        $this->params = array_merge($this->params, $params);
        return $this;
    }

    /**
     * Render a JSON view.
     *
     * @param  array   $data
     * @return string  Rendered output
     */
    public function renderJson($data)
    {
        $jsonData = $this->jsonEncode($data);

        $this->controller->response->type('application/json')->setContent($jsonData);
        return $jsonData;
    }

    /**
     * Serialize array to JSON and used for the response
     *
     * @param  array   $data
     * @return string  Rendered output
     *
     */
    public function jsonEncode($data)
    {
        return json_encode($data);
    }
}
