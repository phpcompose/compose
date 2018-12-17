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
     * @param string $script
     * @param array|null $data
     * @return string
     */
    public function render(string $script, array $data = null) : string;

    /**
     * @param View $view
     * @return string
     */
    public function renderView(View $view) : string;
}