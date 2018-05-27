<?php

namespace ChromeHeadless;

use Symfony\Component\Process\Process;
use Symfony\Component\DomCrawler\Crawler;
use ChromeHeadless\Exceptions\ChromeException;
use Symfony\Component\Process\Exception\ProcessFailedException;

class ChromeHeadless
{
    /**
     * URL of the website.
     *
     * @var string
     */
    protected $url;

    /**
     * DOM of the website as string.
     *
     * @var string
     */
    protected $html;

    /**
     * DOM of the website as crawler object.
     *
     * @var Crawler
     */
    protected $dom;

    /**
     * User agent of the request.
     *
     * @var string
     */
    protected $user_agent;

    /**
     * Viewport of the request.
     *
     * @var array
     */
    protected $viewport;

    /**
     * Additional headers of the request.
     *
     * @var array
     */
    protected $headers;

    /**
     * List of files that should not be loaded.
     *
     * @var array
     */
    protected $blacklist = [];

    /**
     * Path to chrome.
     *
     * @var string
     */
    protected $chrome_path = 'google-chrome';

    /**
     * Timeout in seconds.
     *
     * @var float
     */
    protected $timeout = null;

    /**
     * ChromeHeadless constructor.
     *
     * @param string $url
     */
    public function __construct(string $url = '')
    {
        $this->url = $url;
    }

    /**
     * Set the url of the request and get a new ChromeHeadless instance.
     *
     * @param string $url
     * @return static
     */
    public static function url(string $url)
    {
        return (new static)->setUrl($url);
    }

    /**
     * Set the url.
     *
     * @param string $url
     * @return $this
     */
    public function setUrl(string $url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Set the timeout.
     *
     * @param float $timeout Timeout in seconds.
     * @return $this
     */
    public function setTimeout(float $timeout)
    {
        $this->timeout = $timeout;

        return $this;
    }

    /**
     * Set the content.
     *
     * @param string $html
     * @return $this
     * @throws \ChromeHeadless\Exceptions\ChromeException
     */
    public function setHtml(string $html)
    {
        if (strpos($html, 'Error:') === 0) {
            throw new ChromeException($this->url, $html);
        }

        $this->html = $html;

        return $this;
    }

    /**
     * Set the chrome path.
     *
     * @param string $path
     * @return $this
     */
    public function setChromePath(string $path)
    {
        $this->chrome_path = $path;

        return $this;
    }

    /**
     * Set the user agent.
     *
     * @param string $user_agent
     * @return $this
     */
    public function setUserAgent(string $user_agent)
    {
        $this->user_agent = $user_agent;

        return $this;
    }

    /**
     * Set the viewport.
     *
     * @param mixed $viewport
     */
    public function setViewport($viewport)
    {
        $this->viewport = $viewport;
    }

    /**
     * Set additional request headers.
     *
     * @param mixed $headers
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;
    }

    /**
     * Set a blacklist of files that should not be loaded.
     *
     * @param array $blacklist
     */
    public function setBlacklist(array $blacklist)
    {
        $this->blacklist = $blacklist;
    }

    /**
     * Get the DOM of the website as a Crawler instance.
     *
     * @return Crawler
     * @throws \ChromeHeadless\Exceptions\ChromeException
     */
    public function getDOMCrawler()
    {
        $this->makeRequest();

        $this->dom = new Crawler($this->html);

        return $this->dom;
    }

    /**
     * Get the DOM of the website as string.
     *
     * @return string
     * @throws \ChromeHeadless\Exceptions\ChromeException
     */
    public function getHtml()
    {
        $this->makeRequest();

        return $this->html;
    }

    /**
     * Make the request.
     *
     * @throws \ChromeHeadless\Exceptions\ChromeException
     */
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
     * Generate the command.
     *
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
