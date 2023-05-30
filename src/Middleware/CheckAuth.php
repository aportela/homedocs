<?php

    declare(strict_types=1);

    namespace HomeDocs\Middleware;

    class CheckAuth {

        private $container;

        public function __construct($container) {
            $this->container = $container;
        }

        /**
         * middleware to check api methods with auth required
         *
         * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
         * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
         * @param  callable                                 $next     Next middleware
         *
         * @return \Psr\Http\Message\ResponseInterface
         */
        public function __invoke($request, $response, $next)
        {
            if (\HomeDocs\UserSession::isLogged()) {
                $response = $next($request, $response);
                return $response;
            } else {
                throw new \HomeDocs\Exception\UnauthorizedException($request->getMethod() . " " . $request->getUri()->getPath());
            }
        }
    }
