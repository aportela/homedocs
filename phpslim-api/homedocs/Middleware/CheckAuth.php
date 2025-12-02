<?php

declare(strict_types=1);

namespace HomeDocs\Middleware;

class CheckAuth
{
    protected \Psr\Log\LoggerInterface $logger;

    private readonly string $passphrase;

    protected \aportela\DatabaseWrapper\DB $dbh;

    public function __construct(\Psr\Container\ContainerInterface $container)
    {
        $logger = $container->get(\HomeDocs\Logger\HTTPRequestLogger::class);
        if (! $logger instanceof \HomeDocs\Logger\HTTPRequestLogger) {
            throw new \RuntimeException("Failed to get logger (HTTPRequestLogger) from container");
        }

        $this->logger = $logger;
        $this->passphrase = new \HomeDocs\Settings()->getJWTPassphrase();
        $dbh = $container->get(\aportela\DatabaseWrapper\DB::class);
        if (! $dbh instanceof \aportela\DatabaseWrapper\DB) {
            throw new \RuntimeException("Failed to create database handler from container");
        }

        $this->dbh = $dbh;
    }

    /**
     * middleware to check api methods with auth required
     *
     * @param \Psr\Http\Message\ServerRequestInterface $serverRequest PSR7 request
     * @param \Psr\Http\Server\RequestHandlerInterface $requestHandler PSR7 request handler object
     */
    public function __invoke(\Psr\Http\Message\ServerRequestInterface $serverRequest, \Psr\Http\Server\RequestHandlerInterface $requestHandler): \Psr\Http\Message\ResponseInterface
    {
        // check for valid (non empty/not expired) access token on current session (requires cookies sent by client)
        if (\HomeDocs\UserSession::hasValidAccessToken()) {
            $response = $requestHandler->handle($serverRequest);
            return ($response);
        } else {
            \HomeDocs\UserSession::unsetAccessTokenData();
            // check for valid Authorization Bearer header with (JWT) access token
            $authorizationHeader = $serverRequest->getHeader('Authorization');
            if (empty($authorizationHeader)) {
                throw new \HomeDocs\Exception\UnauthorizedException('Authorization header is missing');
            }
            $bearerToken = $authorizationHeader[0];
            if (strpos($bearerToken, 'Bearer ') !== 0) {
                throw new \HomeDocs\Exception\UnauthorizedException('Invalid Authorization header format');
            }
            $bearerToken = trim(substr($bearerToken, 7));
            if (empty($bearerToken)) {
                throw new \HomeDocs\Exception\UnauthorizedException('Bearer token is missing');
            } else {
                $decoded = null;
                try {
                    $decoded = (new \HomeDocs\JWT($this->logger, $this->passphrase))->decode($bearerToken);
                } catch (\Firebase\JWT\ExpiredException $e) {
                    $this->logger->notice("JWT expired", [$e->getMessage()]);
                    throw new \HomeDocs\Exception\UnauthorizedException("JWT expired");
                } catch (\Throwable $e) {
                    $this->logger->notice("JWT decode error", [$e->getMessage()]);
                    throw new \HomeDocs\Exception\UnauthorizedException("JWT decode error");
                }
                if (property_exists($decoded, "sub") && is_string($decoded->sub) && ! empty($decoded->sub)) {
                    $user = new \HomeDocs\User($decoded->sub);
                    try {
                        $user->get($this->dbh);
                    } catch (\HomeDocs\Exception\NotFoundException) {
                        throw new \HomeDocs\Exception\NotFoundException("userId");
                    }
                    // access token from Authorization Bearer header is good, set new valid session & continue
                    \HomeDocs\UserSession::init($user->id, $user->email);
                    \HomeDocs\UserSession::setAccessTokenData($bearerToken, $decoded->exp);
                } else {
                    throw new \HomeDocs\Exception\UnauthorizedException('Bearer token is missing');
                }
                $response = $requestHandler->handle($serverRequest);
                return ($response);
            }
        }
    }
}
