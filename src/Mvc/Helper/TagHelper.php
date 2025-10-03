<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2017-12-24
 * Time: 3:29 PM
 */

namespace Compose\Mvc\Helper;

use Compose\Mvc\View;

/**
 * Class HtmlHelper
 * @package Compose\Mvc\Helper
 */
class TagHelper implements HelperInterface
{
    public function __invoke(HelperRegistry $helpers, ...$args)
    {
        return $this;
    }

    /**
     * Render open tag
     *
     * @param string $tagName
     * @param array $attributes
     * @param bool $void
     * @return string
     */
    public function open($tagName, array $attributes = null, $void = false) {
        if(!$tagName) return '';
        return '<'. $tagName . $this->attributeString($attributes) .
            (($void) ? ' /' : '') .
            '>';
    }

    /**
     * Render close tag
     *
     * @param string $tagName
     * @param bool $void
     * @return string
     */
    public function close($tagName, $void = false) {
        return ($tagName && !$void) ? '</'.$tagName.'>' : '';
    }

    /**
     * Convert array into key=value string
     *
     * @param array $attributes
     * @return string Will always return an extra empty space
     */
    public function attributeString(array $attributes = null) {
        if(!$attributes) return '';

        $str = '';
        foreach ($attributes as $key => $value) {
            if($value === null) {
                $str .= $key . ' ';
            } else {
                if(!is_scalar($value)) {
                    trigger_error('both value for attribute key {' . $key . '} must be scalar data type');
                }
                $value = View::escape($value);
                $str .= "{$key}=\"{$value}\" ";
            }
        }

        return ' ' . trim($str);
    }

    /**
     * @param $mixed
     * @return string
     */
    public function toString($arg) : string
    {
        if(is_scalar($arg)) return (string) $arg;
        else if(is_array ($arg) || $arg instanceof \Iterator) {
            $buf = '';
            foreach($arg as $val) {
                $buf .= $this->toString($val);
            }
            return $buf;
        }
        else return (string) $arg;
    }

    /**
     * @param string $name
     * @param null $inner
     * @param array|null $attributes
     * @return string
     */
    public function tag(string $name, $inner = null, array $attributes = null) : string
    {
        return  $this->open($name, $attributes) .
                $this->toString($inner) .
                $this->close($name);
    }
}
