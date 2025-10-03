<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2018-06-24
 * Time: 3:43 PM
 */

namespace Compose\Mvc\Helper;

class LayoutHelper implements HelperInterface
{
    protected
        $content,
        $sections,
        $data = [];

    private ?HelperRegistry $helpers = null;

    public function __invoke(HelperRegistry $helpers, ...$args)
    {
        $this->helpers = $helpers;
        return $this;
    }

    /**
     * @param string $key
     * @param $data
     */
    public function share(string $key, $data)
    {
        $this->data[$key] = $data;
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function shared(string $key)
    {
        return $this->data[$key] ?? null;
    }

    /**
     * Stores current layout main content
     *
     * This method will be called by view renderer to store view content
     * @param string|null $content
     * @return null|string
     */
    public function content(string $content = null) : ?string
    {
        if($content !== null) {
            $this->content = $content;
            return null;
        }

        return $this->content;
    }

    /**
     * Starts a section
     *
     * @param string $name
     */
    public function start(string $name) : void
    {
        ob_start(function($buffer) use ($name) {
            $this->sections[$name] = $buffer;
            return $buffer;
        });
    }

    /**
     * ends previous started section
     */
    public function end() : void
    {
        ob_end_clean();
    }

    /**
     * @param string $name
     * @return null|string
     */
    public function section(string $name) : ?string
    {
        return $this->sections[$name] ?? null;
    }
}
