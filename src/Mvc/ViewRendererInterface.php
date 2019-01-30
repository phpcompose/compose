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
     * @param View $view
     * @return string
     */
    public function render(View $view) : string;
}