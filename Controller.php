<?php


namespace core_fw;


use core_fw\Application;

class Controller
{
    public string $layout = 'main';

    /**
     * override the layout
     * @param $layout
     */
    public function setLayout($layout)
    {
        $this->layout = $layout;
    }

    /**
     * set view according to the controller
     * @param $view
     * @param array $params
     * @return array|false|string|string[]
     */
    public function render($view, $params = [])
    {
        return Application::$app->router->renderView($view, $params);
    }

}