<?php

declare(strict_types=1);

namespace HomeDocs\Middleware;

class JWT
{
    protected \Psr\Log\LoggerInterface $logger;

    private readonly string $passphrase;

    protected \aportela\DatabaseWrapper\DB $dbh;

    public function __construct(\Psr\Container\ContainerInterface $container)
    {
        $logger = $container->get(\HomeDocs\Logger\DefaultLogger::class);
        if (! $logger instanceof \HomeDocs\Logger\DefaultLogger) {
            throw new \RuntimeException("Failed to get logger (DefaultLogger) from container");
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
     * middleware to manage JWT authentication HTTP header
     *
     * @param \Psr\Http\Message\ServerRequestInterface $serverRequest PSR7 request
     * @param \Psr\Http\Server\RequestHandlerInterface $requestHandler PSR7 request handler object
     */
    public function __invoke(\Psr\Http\Message\ServerRequestInterface $serverRequest, \Psr\Http\Server\RequestHandlerInterface $requestHandler): \Psr\Http\Message\ResponseInterface
    {
        // extract the Bearer Token from the Authorization header
        $authorizationHeader = $serverRequest->getHeader('Authorization');
        if (empty($authorizationHeader)) {
            throw new \HomeDocs\Exception\UnauthorizedException();
        } else {
            $bearerToken = trim(preg_replace('/Bearer\s/', '', $authorizationHeader[0]));
            // user not logged (or session lost) && jwt auth bearer header found => re-validate session with jwt
            if (!\HomeDocs\UserSession::isLogged() && !empty($bearerToken)) {
                // try decoding jwt data
                $jwt = new \HomeDocs\JWT($this->logger, $this->passphrase);
                $decoded = $jwt->decode($bearerToken);
                if (property_exists($decoded, "data") && is_object($decoded->data) && property_exists($decoded->data, "userId") && is_string($decoded->data->userId) && property_exists($decoded->data, "email") && is_string($decoded->data->email)) {
                    $this->logger->notice("JWT valid data decoded", [print_r($decoded->data, true)]);
                    $user = new \HomeDocs\User($decoded->data->userId);
                    if ($user->exists($this->dbh)) {
                        \HomeDocs\UserSession::init($decoded->data->userId, $decoded->data->email);
                    } else {
                        throw new \HomeDocs\Exception\NotFoundException("userId");
                    }
                } else {
                    throw new \HomeDocs\Exception\InvalidParamsException("jwt");
                }
            }
            $response = $requestHandler->handle($serverRequest);
            return ($response);
        }
    }
}
