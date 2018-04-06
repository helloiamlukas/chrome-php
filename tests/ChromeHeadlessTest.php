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
        $this->assertContains('Example Domain', $crawler->filter('body h1')->text());
    }

    /** @test */
    public function it_can_handle_an_invalid_chrome_path()
    {
        $this->expectException(ProcessFailedException::class);

        ChromeHeadless::url('https://example.com')
            ->setChromePath('invalid/chrome/path')
            ->getHtml();
    }

    /** @test */
    public function it_can_set_a_custom_user_agent()
    {
        $user_agent = 'NiceUserAgent/1.0';

        $command = ChromeHeadless::url('https://example.com')
            ->setChromePath('google-chrome')
            ->setUserAgent($user_agent)
            ->createCommand();

        $this->assertContains('--user-agent="'.$user_agent.'"', $command);
    }

    /** @test */
    public function it_can_detect_empty_responses()
    {
        $this->expectException(EmptyDocument::class);

        ChromeHeadless::url('https://www.this-url-does-not-exist-0321980381.com')
            ->getHtml();
    }

    /** @test */
    public function it_can_set_and_detect_a_timeout()
    {
        $this->expectException(ProcessTimedOutException::class);

        var_dump(ChromeHeadless::url('https://example.com')
            ->setTimeout(0.01)
            ->getHtml());
    }
}
