<?php

namespace App\Api;

use App\Helper\PHPSession;
use App\Helper\Twig;
use Illuminate\Support\Collection;
use MongoDB\BSON\UTCDateTime;
use Nyholm\Psr7\Stream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

abstract class AbstractController
{
    public bool $protected = false;
    protected ServerRequestInterface $request;
    protected ResponseInterface $response;
    private array $preload = [];
    protected Collection $arguments;

    public function __construct(
        protected PHPSession $session,
        protected Twig $twig
    ) {

    }

    public function __invoke(string $actionName = 'handle'): \Closure
    {
        $controller = $this;

        return function(ServerRequestInterface $request, ResponseInterface $response, array $args) use ($controller, $actionName) {
            $controller->arguments = new Collection();
            $controller->setRequest($request);
            $controller->setResponse($response);

            return call_user_func_array([$controller, $actionName], $args);
        };
    }

    protected function setRequest(ServerRequestInterface $request): void
    {
        $this->request = $request;
    }

    protected function setResponse(ResponseInterface $response): void
    {
        $this->response = $response;
    }

    /**
     * Get a single route argument
     *
     * @param string $key
     *
     * @return string|null|bool
     */
    protected function getArg(string $key): string|null|bool
    {
        if ($this->getArgs()->has($key)) {
            return $this->getArgs()->get($key);
        }
        return null;
    }

    /**
     * Get route arguments
     *
     * @return Collection
     */
    protected function getArgs(): Collection
    {
        return $this->arguments;
    }

    /**
     * Return a single POST/GET Param
     *
     * @param string $key
     *
     * @return string|bool|null
     */
    protected function getParam(string $key): string|null|bool
    {
        return $this->getParams()->get($key);
    }

    /**
     * Return all POST/GET Params
     *
     * @return Collection
     */
    protected function getParams(): Collection
    {
        return new Collection($this->request->getQueryParams());
    }

    /**
     * Return a single POST Param
     *
     * @param string $key
     *
     * @return string|null|bool
     */
    protected function getPostParam(string $key): string|null|bool
    {
        return $this->getPostParams()->get($key);
    }

    /**
     * Return all POST Params
     *
     * @return Collection
     */
    protected function getPostParams(): Collection
    {
        $post = array_diff_key($this->request->getParsedBody(), array_flip([
            '_METHOD',
        ]));

        return new Collection($post);
    }

    /**
     * Get the files posted
     *
     * @return Collection
     */
    protected function getFiles(): Collection
    {
        $files = array_diff_key($this->request->getUploadedFiles(), array_flip([
            '_METHOD',
        ]));

        return new Collection($files);
    }

    /**
     * Get a single request header
     *
     * @param string $key
     *
     * @return string|null
     */
    protected function getHeader(string $key): ?string
    {
        return $this->getHeaders()->get($key);
    }

    /**
     * Get all request headers
     *
     * @return Collection
     */
    protected function getHeaders(): Collection
    {
        return new Collection($this->request->getHeaders());
    }

    /**
     * Tells the web client to preload a resource, can be image, css, media, etc.
     * Refer to https://www.w3.org/TR/preload/#server-push-(http/2) for more info
     *
     * @param string $urlPath local path or remote http/https
     */
    protected function preload(string $urlPath): void
    {
        $this->preload[] = "<{$urlPath}>; rel=preload;";
    }

    /**
     * @param $dateTime
     *
     * @return UTCDateTime
     */
    protected function makeTimeFromDateTime(string $dateTime): UTCDateTime
    {
        $unixTime = strtotime($dateTime);
        $milliseconds = $unixTime * 1000;

        return new UTCDateTime($milliseconds);
    }

    /**
     * @param $unixTime
     *
     * @return UTCDateTime
     */
    protected function makeTimeFromUnixTime(int $unixTime): UTCDateTime
    {
        $milliseconds = $unixTime * 1000;

        return new UTCDateTime($milliseconds);
    }

    /**
     * Output html data
     *
     * @param string $htmlData
     * @param int $cacheTime
     * @param int $status
     * @param string $contentType
     *
     * @return ResponseInterface
     */
    protected function html(string $htmlData, int $cacheTime = 0, int $status = 200, string $contentType = 'text/html'): ResponseInterface
    {
        $response = $this->generateResponse($status, $contentType, $cacheTime);
        $body = $this->body($htmlData);

        return $response->withBody($body);
    }

    /**
     * Render the data as json output
     *
     * @param array|Collection $data
     * @param int $status
     * @param String $contentType
     * @param int $cacheTime
     *
     * @return ResponseInterface
     * @throws \JsonException
     */
    protected function json(array|Collection $data = [], int $cacheTime = 30, int $status = 200, string $contentType = 'application/json; charset=UTF-8'): ResponseInterface
    {
        $response = $this->generateResponse($status, $contentType, $cacheTime);
        $body = $this->body(json_encode($data, JSON_THROW_ON_ERROR | JSON_NUMERIC_CHECK));

        return $response->withBody($body);
    }

    /**
     * Renders a twig template
     *
     * @param string $template
     * @param array|Collection $data
     * @param int $cacheTime
     * @param int $status
     * @param string $contentType
     *
     * @return ResponseInterface
     */
    protected function render(string $template, array|Collection $data = [], int $cacheTime = 0, int $status = 200, string $contentType = 'text/html'): ResponseInterface
    {
        $render = $this->twig->render($template, $data);
        $response = $this->generateResponse($status, $contentType, $cacheTime);
        $body = $this->body($render);
        return $response->withBody($body);
    }
    /**
     * Generates the response for the output types, render, json, xml and html
     *
     * @param int $status
     * @param string $contentType
     * @param int $cacheTime
     *
     * @return ResponseInterface
     */
    protected function generateResponse(int $status, string $contentType, int $cacheTime): ResponseInterface
    {
        $response = $this->response->withStatus($status)
            ->withHeader('Content-Type', $contentType)
            ->withAddedHeader('Access-Control-Allow-Origin', '*')
            ->withAddedHeader('Access-Control-Allow-Methods', '*');

        if ($cacheTime > 0) {
            $response = $response
                ->withAddedHeader('Expires', gmdate('D, d M Y H:i:s', time() + $cacheTime))
                ->withAddedHeader('Cache-Control', "public, max-age={$cacheTime}, proxy-revalidate");
        }

        if (!empty($this->preload)) {
            foreach ($this->preload as $preload) {
                $response = $response->withAddedHeader('Link', $preload);
            }
        }

        return $response;
    }

    /**
     * Generate a new body with input data
     *
     * @param $data
     *
     * @return StreamInterface
     */
    private function body($data): StreamInterface
    {
        $body = Stream::create();
        $body->write($data);

        return $body;
    }

    /**
     * Get the full path of the request (http://mydomain.tld/request/requestData)
     * @return string
     */
    protected function getFullPath(): string
    {
        $port = $this->request->getServerParams()['SERVER_PORT'];

        return "{$this->request->getUri()->getScheme()}://{$this->request->getUri()->getHost()}:{$port}/{$this->request->getUri()->getPath()}";
    }

    /**
     * Get the full host of the request (http://mydomain.tld/)
     * @return string
     */
    protected function getFullHost(): string
    {
        $port = $this->request->getServerParams()['SERVER_PORT'];

        return "{$this->request->getUri()->getScheme()}://{$this->request->getUri()->getHost()}:{$port}";
    }

    /**
     * Redirect.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * This method prepares the response object to return an HTTP Redirect
     * response to the client.
     *
     * @param string $url The redirect destination.
     *
     * @return ResponseInterface
     */
    protected function redirect(string $url): ResponseInterface
    {
        return $this->response->withAddedHeader('Location', $url);
    }
}