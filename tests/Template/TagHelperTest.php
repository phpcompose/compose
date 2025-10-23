<?php

declare(strict_types=1);

namespace Tests\Template;

use Compose\Template\Helper\TagHelper;
use Compose\Template\Template;
use PHPUnit\Framework\TestCase;

final class TagHelperTest extends TestCase
{
    public function testAttributeStringBooleanAndClassArrayAndData(): void
    {
        $helper = new TagHelper();

        $attrs = [
            'disabled' => true,
            'href' => 'a"b',
            'class' => ['one', 'two', ''],
            'data-meta' => ['k' => 'v'],
        ];

        $out = $this->invokeAttrString($helper, $attrs);

        $this->assertStringContainsString(' disabled', $out);
        // Template::escape() should HTML-escape quotes to &quot;
        $this->assertStringContainsString(' href="a&quot;b"', $out);
        $this->assertStringContainsString(' class="one two"', $out);
        $this->assertStringContainsString(' data-meta="', $out);
        // data-meta should be json encoded and HTML-escaped (quotes become &quot;)
        $this->assertStringContainsString('&quot;k&quot;:&quot;v&quot;', $out);
    }

    public function testVoidTagWithInnerTextIsRenderedLikeBrowser(): void
    {
        $helper = new TagHelper();

        $html = $helper->tag('img', 'caption', ['src' => 'x.png']);

        // Should render opening tag and then the inner text, without a closing </img>
        $this->assertStringContainsString('<img', $html);
        $this->assertStringContainsString('src="x.png"', $html);
        $this->assertStringContainsString('caption', $html);
        $this->assertStringNotContainsString('</img>', $html);
    }

    private function invokeAttrString(TagHelper $h, array $attrs): string
    {
        // access protected method attributeString by invoking via tag helper open which calls it
        // create a dummy tag so output contains the attribute string
        return $h->open('a', $attrs);
    }
}
