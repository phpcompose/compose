<?php
/**
 * Created by PhpStorm.
 * User: Alamin
 */

namespace Compose\Mvc;


interface ViewRendererInterface
{
    /**
     * @param string $script
     * @param array $data
     * @return mixed
     */
    public function render(string $script, array $data = []);
}