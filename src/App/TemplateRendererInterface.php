<?php
namespace Compose\App;

interface TemplateRendererInterface
{
    public function render(string $name, array $data = null);
}