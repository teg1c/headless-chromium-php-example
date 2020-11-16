<?php


use HeadlessChromium\Browser;
use HeadlessChromium\BrowserFactory;
use HeadlessChromium\Communication\Connection;
use HeadlessChromium\Cookies\Cookie;
use HeadlessChromium\Page;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yurun\Util\HttpRequest;
use Yurun\Util\YurunHttp\Http\Response;

class ChromeDownloader
{
    /**
     * 可执行文件路径或http接口地址
     *
     * @var string
     */
    protected $path = "http://127.0.0.1:9222/json";

    /**
     * 创建浏览器的参数
     *
     * @var array
     */
    protected $options = [];

    public function download(ServerRequestInterface $request): ResponseInterface
    {
        $browser = $this->createBrowser($needClose);
        try {
            $page = $browser->createPage();
            // cookies
            $cookies = [];
            foreach ($request->getCookieParams() as $k => $v) {
                $cookies[] = Cookie::create($k, $v);
            }
            $page->setCookies($cookies);
            $page->navigate($request->getUri()->__toString())->waitForNavigation(Page::LOAD);
            // body
            $body = $page->evaluate('document.documentElement.outerHTML')->getReturnValue();
            $page->evaluate(
                'console.log(111111111);(() => {
                                document.querySelector("#yhm").value = "1420111026";
                                document.querySelector("#mm").value = "karen0324";
                                document.querySelector("#dl").click();
                            })()'
            );
            $response = new Response($body);
            // cookie
            $cookieStr = $page->evaluate('document.cookie')->getReturnValue();
            if ($cookieStr) {
                $cookieOriginParams = [];
                foreach (explode(';', $cookieStr) as $item) {
                    [$name, $value] = explode('=', $item, 2);
                    $cookieOriginParams[trim($name)] = [
                        'value' => $value,
                    ];
                }
                $response = $response->withCookieOriginParams($cookieOriginParams);
            }
            return $response;
        } finally {
            if (isset($page)) {
                $page->close();
            }
            if ($needClose) {
                $browser->close();
            }
        }
    }

    public function createBrowser(?bool &$needClose): Browser
    {
        if ('http://' === substr($this->path, 0, 7)) {
            $http = new HttpRequest;
            $data = $http->get($this->path)->json(true);
            if (!isset($data[0]['webSocketDebuggerUrl'])) {
                throw new \RuntimeException('Not found webSocketDebuggerUrl');
            }
            $connection = new Connection(end($data)['webSocketDebuggerUrl']);
            $connection->connect();
            if (!$connection->isConnected()) {
                throw new \RuntimeException('Connect to chrome failed');
            }
            $needClose = false;
            return new Browser($connection);
        } else {
            $factory = new BrowserFactory($this->path);
            $needClose = true;
            $options = $this->options;

            return $factory->createBrowser($options);
        }
    }
}