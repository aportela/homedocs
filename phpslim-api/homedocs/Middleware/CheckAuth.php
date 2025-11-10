<?php

declare(strict_types=1);

namespace HomeDocs\Middleware;

class CheckAuth
{
    /**
     * middleware to check api methods with auth required
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param  \Psr\Http\Server\RequestHandlerInterface $handler  PSR7 request handler object
     */
    public function __invoke(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Server\RequestHandlerInterface $handler): \Psr\Http\Message\ResponseInterface
    {
        if (\HomeDocs\UserSession::isLogged()) {
            return $handler->handle($request);
        } else {
            throw new \HomeDocs\Exception\UnauthorizedException($request->getMethod() . " " . $request->getUri()->getPath());
        }
    }
}
