<?php

namespace ChromeHeadless;

use Symfony\Component\Process\Process;
use Symfony\Component\DomCrawler\Crawler;
use ChromeHeadless\Exceptions\EmptyDocument;
use Symfony\Component\Process\Exception\ProcessFailedException;

class ChromeHeadless
{
    protected $url;
    protected $html;
    protected $dom;
    protected $user_agent;
    protected $chrome_path = 'google-chrome';
    protected $timeout = 10;

    public function __construct(string $url = '')
    {
        $this->url = $url;
    }

    /**
     * @param string $url
     * @return static
     */
    public static function url(string $url)
    {
        return (new static)->setUrl($url);
    }

    public function setUrl(string $url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @param float $timeout Timeout in seconds.
     * @return $this
     */
    public function setTimeout(float $timeout)
    {
        $this->timeout = $timeout;

        return $this;
    }

    public function setHtml(string $html)
    {
        if (strpos($html, '<html><head></head><body></body></html>') !== false) {
            throw new EmptyDocument($this->url);
        }

        $this->html = $html;

        return $this;
    }

    public function setChromePath(string $path)
    {
        $this->chrome_path = $path;

        return $this;
    }

    public function setUserAgent(string $user_agent)
    {
        $this->user_agent = $user_agent;

        return $this;
    }

    /**
     * @return Crawler
     */
    public function getDOMCrawler()
    {
        $this->makeRequest();

        $this->dom = new Crawler($this->html);

        return $this->dom;
    }

    /**
     * @return mixed
     */
    public function getHtml()
    {
        $this->makeRequest();

        return $this->html;
    }

    protected function makeRequest()
    {
        $command = $this->createCommand();

        $chrome = new Process($command);
        $chrome->setTimeout($this->timeout);
        $chrome->run();

        if (! $chrome->isSuccessful()) {
            throw new ProcessFailedException($chrome);
        }

        $this->setHtml($chrome->getOutput());
    }

    /**
     * @return array
     */
    public function createCommand()
    {
        $command = [$this->chrome_path, '--headless', '--dump-dom', $this->url];

        if (! empty($this->user_agent)) {
            array_push($command, '--user-agent="'.$this->user_agent.'"');
        }

        return $command;
    }
}
