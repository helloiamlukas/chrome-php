<?php

namespace ChromeHeadless\Test;

use PHPUnit\Framework\TestCase;
use ChromeHeadless\ChromeHeadless;
use ChromeHeadless\Exceptions\ChromeException;
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

    /** @test */
    public function it_can_detect_an_unsuccessful_http_response()
    {
        $this->expectException(ChromeException::class);

        ChromeHeadless::url('https://httpstat.us/500')->getHtml();
    }

    /** @test */
    public function it_can_detect_a_invalid_request()
    {
        $this->expectException(ChromeException::class);

        ChromeHeadless::url('https://thiswebsitedoesnotexistatall912393124.com')->getHtml();
    }
}
