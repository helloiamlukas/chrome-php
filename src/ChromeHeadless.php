<?php

namespace ChromeHeadless;

use ChromeHeadless\Exceptions\ChromeException;
use ChromeHeadless\Exceptions\CloudflareProtection;
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

    protected $viewport;

    protected $headers;

    protected $blacklist = [];

    protected $chrome_path = 'google-chrome';

    protected $timeout = null;

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
        if (strpos($html, 'Error:') === 0) {
            throw new ChromeException($this->url, $html);
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
     * @param mixed $viewport
     */
    public function setViewport($viewport)
    {
        $this->viewport = $viewport;
    }

    /**
     * @param mixed $headers
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;
    }

    /**
     * @param array $blacklist
     */
    public function setBlacklist(array $blacklist)
    {
        $this->blacklist = $blacklist;
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

        if (! is_null($this->timeout)) {
            $chrome->setTimeout($this->timeout);
        }

        $chrome->run();

        if (! $chrome->isSuccessful()) {
            throw new ProcessFailedException($chrome);
        }

        $this->setHtml($chrome->getOutput());
    }

    /**
     * @return string
     */
    public function createCommand()
    {
        $options = [
            'url' => $this->url,
            'path' => $this->chrome_path,
        ];

        if (! empty($this->user_agent)) {
            $options['userAgent'] = $this->user_agent;
        }

        if (! empty($this->viewport)) {
            $options['viewport'] = $this->viewport;
        }

        if (! empty($this->headers)) {
            $options['headers'] = $this->headers;
        }

        if (! empty($this->blacklist)) {
            $options['blacklist'] = $this->blacklist;
        }

        $command = [
            'NODE_PATH=`npm root -g`',
            'node',
            __DIR__.'/../bin/chrome.js',
            escapeshellarg(json_encode($options)),
        ];

        return implode(' ', $command);
    }
}
