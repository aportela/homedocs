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
        $this->logger = $container->get(\HomeDocs\Logger\HTTPRequestLogger::class);
        $this->passphrase = $container->get('settings')['jwt']['passphrase'];
        $this->dbh = $container->get(\aportela\DatabaseWrapper\DB::class);
    }

    /**
     * middleware to manage JWT authentication HTTP header
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param  \Psr\Http\Server\RequestHandlerInterface $handler  PSR7 request handler object
     */
    public function __invoke(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Server\RequestHandlerInterface $handler): \Psr\Http\Message\ResponseInterface
    {
        $clientHeaderJWT = $request->hasHeader("HOMEDOCS-JWT") ? $request->getHeader("HOMEDOCS-JWT")[0] : null;
        // user not logged (or session lost) && jwt auth header found => re-auth with jwt
        if (!\HomeDocs\UserSession::isLogged() && !empty($clientHeaderJWT)) {
            // try decoding jwt data
            $jwt = new \HomeDocs\JWT($this->logger, $this->passphrase);
            $decoded = $jwt->decode($clientHeaderJWT);
            if (isset($decoded->data) && isset($decoded->data->userId) && isset($decoded->data->email)) {
                $this->logger->notice("JWT valid data decoded", [print_r($decoded->data, true)]);
                $user = new \HomeDocs\User($decoded->data->userId);
                if ($user->exists($this->dbh)) {
                    \HomeDocs\UserSession::set($decoded->data->userId, $decoded->data->email);
                } else {
                    throw new \HomeDocs\Exception\NotFoundException("userId");
                }
            } else {
                throw new \HomeDocs\Exception\InvalidParamsException("jwt");
            }
            $response = $handler->handle($request);
            if (!empty($clientHeaderJWT)) {
                return $response->withHeader("HOMEDOCS-JWT", $clientHeaderJWT);
            } else {
                return ($response);
            }
        } else {
            $response = $handler->handle($request);
            if (empty($clientHeaderJWT)) {
                if (\HomeDocs\UserSession::isLogged()) {
                    $payload = [
                        "userId" => $_SESSION["userId"] ?? null,
                        "email" => $_SESSION["email"] ?? null
                    ];
                    $jwt = new \HomeDocs\JWT($this->logger, $this->passphrase);
                    $clientHeaderJWT = $jwt->encode($payload);
                }
            }
            if ($clientHeaderJWT && \HomeDocs\UserSession::isLogged()) {
                return $response->withHeader("HOMEDOCS-JWT", $clientHeaderJWT);
            } else {
                return ($response);
            }
        }
    }
}
