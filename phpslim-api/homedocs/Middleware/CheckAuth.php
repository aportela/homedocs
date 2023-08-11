<?php

declare(strict_types=1);

namespace HomeDocs\Middleware;

class CheckAuth
{
    public function __construct(\Psr\Container\ContainerInterface $container)
    {
    }

    /**
     * middleware to check api methods with auth required
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param  \Psr\Http\Server\RequestHandlerInterface $handler  PSR7 request handler object
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Server\RequestHandlerInterface $handler): \Psr\Http\Message\ResponseInterface
    {
        if (\HomeDocs\UserSession::isLogged()) {
            $response = $handler->handle($request);
            return $response;
        } else {
            throw new \HomeDocs\Exception\UnauthorizedException($request->getMethod() . " " . $request->getUri()->getPath());
        }
    }
}
