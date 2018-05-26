<?php

namespace ChromeHeadless\Test;

use PHPUnit\Framework\TestCase;
use ChromeHeadless\ChromeHeadless;
use ChromeHeadless\Exceptions\EmptyDocument;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;

class ChromeHeadlessTest extends TestCase
{
    /** @test */
    public function it_can_get_the_html()
    {
        $html = ChromeHeadless::url('https://example.com')->getHtml();
        $this->assertContains('<h1>Example Domain</h1>', $html);
    }

    /** @test */
    public function it_can_get_the_dom()
    {
        $crawler = ChromeHeadless::url('https://example.com')->getDOMCrawler();
        $this->assertContains('Example Domain',
                              $crawler->filter('body h1')->text());
    }

    /** @test */
    public function it_can_set_and_detect_a_timeout()
    {
        $this->expectException(ProcessTimedOutException::class);

        ChromeHeadless::url('https://example.com')->setTimeout(0.01)->getHtml();
    }
}
