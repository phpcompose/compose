<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2017-10-29
 * Time: 12:42 PM
 */

namespace Compose\Mvc;


/**
 * Interface ViewRendererInterface
 * @package Compose\Mvc
 */
interface ViewRendererInterface
{
    /**
     * Renders a given $script with optional $data
     *
     * @param string $script
     * @param null $data
     * @return string
     */
    public function render(string $script, array $data = null) : string;
    public function renderView(View $view) : string;
}