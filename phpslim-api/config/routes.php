<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

return function (\Slim\App $app): void {

    $app->get('/', function (Request $request, Response $response, array $args): \Psr\Http\Message\MessageInterface|\Psr\Http\Message\ResponseInterface {
        $filePath = dirname(__DIR__) . '/public/index.html';
        if (file_exists($filePath)) {
            $contents = file_get_contents($filePath);
            if (is_string($contents)) {
                $response->getBody()->write($contents);
                return $response->withHeader('Content-Type', 'text/html; charset=UTF-8');
            } else {
                $response->getBody()->write("Invalid html template");
                return $response->withStatus(500);
            }
        } else {
            return $response->withStatus(404);
        }
    });

    $app->group(
        '/api3',
        function (RouteCollectorProxy $routeCollectorProxy) use ($app): void {
            $container = $app->getContainer();
            if (!$container instanceof \Psr\Container\ContainerInterface) {
                throw new \RuntimeException("Error getting container");
            }

            $settings = new \HomeDocs\Settings();
            $serverEnvironment = \HomeDocs\Utils::GetServerEnvironment($settings);

            $routeCollectorProxy->get('/server_environment', function (Request $request, Response $response, array $args) use ($serverEnvironment): \Psr\Http\Message\MessageInterface {
                $payload = \HomeDocs\Utils::getJSONPayload(
                    [
                        'serverEnvironment' => $serverEnvironment,
                    ]
                );
                $response->getBody()->write($payload);
                return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
            });

            $routeCollectorProxy->group('/auth', function (RouteCollectorProxy $routeCollectorProxy) use ($container, $settings): void {
                $routeCollectorProxy->post('/register', function (Request $request, Response $response, array $args) use ($container, $settings): \Psr\Http\Message\MessageInterface {
                    if ($settings->allowSignUp()) {
                        $params = $request->getParsedBody();
                        if (! is_array($params)) {
                            throw new \HomeDocs\Exception\InvalidParamsException();
                        }

                        $dbh = $container->get(\aportela\DatabaseWrapper\DB::class);
                        if (! $dbh instanceof \aportela\DatabaseWrapper\DB) {
                            throw new \RuntimeException("Failed to create database handler from container");
                        }

                        if (\HomeDocs\User::isEmailUsed($dbh, array_key_exists("email", $params) && is_string($params["email"]) ? $params["email"] : "")) {
                            throw new \HomeDocs\Exception\AlreadyExistsException("email");
                        } else {
                            $user = new \HomeDocs\User(
                                array_key_exists("id", $params) && is_string($params["id"]) ? $params["id"] : "",
                                array_key_exists("email", $params) && is_string($params["email"]) ? $params["email"] : "",
                                array_key_exists("password", $params) && is_string($params["password"]) ? $params["password"] : ""
                            );
                            $user->add($dbh);
                            $payload = \HomeDocs\Utils::getJSONPayload(
                                []
                            );
                            $response->getBody()->write($payload);
                            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
                        }
                    } else {
                        throw new \HomeDocs\Exception\AccessDeniedException("");
                    }
                });

                $routeCollectorProxy->post('/login', function (Request $request, Response $response, array $args) use ($container, $settings): \Psr\Http\Message\MessageInterface {
                    $params = $request->getParsedBody();
                    if (! is_array($params)) {
                        throw new \HomeDocs\Exception\InvalidParamsException();
                    }

                    $dbh = $container->get(\aportela\DatabaseWrapper\DB::class);
                    if (! $dbh instanceof \aportela\DatabaseWrapper\DB) {
                        throw new \RuntimeException("Failed to create database handler from container");
                    }

                    $logger = $container->get(\HomeDocs\Logger\DefaultLogger::class);
                    if (! $logger instanceof \HomeDocs\Logger\DefaultLogger) {
                        throw new \RuntimeException("Failed to create logger from container");
                    }

                    $user = new \HomeDocs\User(
                        "",
                        array_key_exists("email", $params) && is_string($params["email"]) ? $params["email"] : "",
                        array_key_exists("password", $params) && is_string($params["password"]) ? $params["password"] : ""
                    );
                    $user->login($dbh);

                    $jwt = new \HomeDocs\JWT($logger, $settings->getJWTPassphrase());

                    $currentTimestamp = time();
                    $accessToken = $jwt->encode(strval(\HomeDocs\UserSession::getUserId()), $currentTimestamp + $settings->getAccessTokenExpirationTimeInSeconds());
                    $refreshToken = $jwt->encode(strval(\HomeDocs\UserSession::getUserId()), $currentTimestamp + $settings->getRefreshTokenExpirationTimeInSeconds());
                    \HomeDocs\UserSession::setAccessTokenData($accessToken, $currentTimestamp + $settings->getAccessTokenExpirationTimeInSeconds());
                    $payload = \HomeDocs\Utils::getJSONPayload(
                        [
                            "accessToken" => [
                                "token" => $accessToken,
                                "expiresAtTimestamp" => $currentTimestamp + $settings->getAccessTokenExpirationTimeInSeconds(),
                            ],
                            "refreshToken" => [
                                "token" => $refreshToken,
                                "expiresAtTimestamp" => $currentTimestamp + $settings->getRefreshTokenExpirationTimeInSeconds(),
                            ],
                            "tokenType" => "Bearer",
                        ]
                    );
                    setcookie(
                        "refresh_token",
                        $refreshToken,
                        [
                            'expires' => $currentTimestamp + $settings->getRefreshTokenExpirationTimeInSeconds(),
                            'path' => '/api3/auth/renew_access_token',
                            'secure' => true,
                            'httponly' => true,
                            'samesite' => 'Strict',
                        ]
                    );
                    $response->getBody()->write($payload);
                    return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
                });

                $routeCollectorProxy->post('/renew_access_token', function (Request $request, Response $response, array $args) use ($container, $settings): \Psr\Http\Message\MessageInterface {
                    $refreshToken = null;
                    if (isset($_COOKIE['refresh_token']) && ! empty($_COOKIE['refresh_token'])) {
                        $refreshToken = $_COOKIE['refresh_token'];
                    } else {
                        $params = $request->getParsedBody();
                        if (is_array($params) && array_key_exists("refreshToken", $params) && is_string($params["refreshToken"]) && ($params["refreshToken"] !== '' && $params["refreshToken"] !== '0')) {
                            $refreshToken = $params["refreshToken"];
                        }
                    }

                    if (! is_string($refreshToken) || ($refreshToken === '' || $refreshToken === '0')) {
                        throw new \HomeDocs\Exception\UnauthorizedException("Missing refresh token (cookie/POST param)");
                    }

                    $dbh = $container->get(\aportela\DatabaseWrapper\DB::class);
                    if (! $dbh instanceof \aportela\DatabaseWrapper\DB) {
                        throw new \RuntimeException("Failed to create database handler from container");
                    }

                    $logger = $container->get(\HomeDocs\Logger\DefaultLogger::class);
                    if (! $logger instanceof \HomeDocs\Logger\DefaultLogger) {
                        throw new \RuntimeException("Failed to create logger from container");
                    }

                    $jwt = new \HomeDocs\JWT($logger, $settings->getJWTPassphrase());
                    $decoded = null;
                    try {
                        $decoded = $jwt->decode($refreshToken);
                    } catch (\Firebase\JWT\ExpiredException $e) {
                        $logger->notice("JWT expired", [$e->getMessage()]);
                        throw new \HomeDocs\Exception\UnauthorizedException("JWT expired");
                    } catch (\Throwable $e) {
                        $logger->notice("JWT decode error", [$e->getMessage()]);
                        throw new \HomeDocs\Exception\UnauthorizedException("JWT decode error");
                    }

                    if (property_exists($decoded, "sub") && is_string($decoded->sub) && ($decoded->sub !== '' && $decoded->sub !== '0')) {
                        $user = new \HomeDocs\User($decoded->sub);
                        $user->get($dbh);
                        $jwt = new \HomeDocs\JWT($logger, $settings->getJWTPassphrase());
                        $currentTimestamp = time();
                        $accessToken = $jwt->encode(strval($user->id), $currentTimestamp + $settings->getAccessTokenExpirationTimeInSeconds());
                        \HomeDocs\UserSession::init($user->id, $user->email);
                        \HomeDocs\UserSession::setAccessTokenData($accessToken, $currentTimestamp + $settings->getAccessTokenExpirationTimeInSeconds());
                        $payload = \HomeDocs\Utils::getJSONPayload(
                            [
                                "accessToken" => [
                                    "token" => $accessToken,
                                    "expiresAtTimestamp" => $currentTimestamp + $settings->getAccessTokenExpirationTimeInSeconds(),
                                ],
                                "tokenType" => "Bearer",
                            ]
                        );
                        $response->getBody()->write($payload);
                        return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
                    } else {
                        throw new \HomeDocs\Exception\UnauthorizedException("Missing user id on JWT refresh token");
                    }
                });

                $routeCollectorProxy->post('/logout', function (Request $request, Response $response, array $args): \Psr\Http\Message\MessageInterface {
                    \HomeDocs\User::logout();
                    $payload = \HomeDocs\Utils::getJSONPayload(
                        []
                    );
                    setcookie(
                        "refresh_token",
                        "",
                        [
                            'expires' => time() - 3600,
                            'path' => '/api3/auth/renew_access_token',
                            'secure' => true,
                            'httponly' => true,
                            'samesite' => 'Strict',
                        ]
                    );
                    $response->getBody()->write($payload);
                    return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
                });
            });

            $routeCollectorProxy->get('/shared/attachment/{attachment_id}', function (Request $request, Response $response, array $args) use ($container): \Psr\Http\Message\MessageInterface {

                $params = $request->getQueryParams();

                if (! (array_key_exists("id", $params) && is_string($params['id']) && (($params['id'] !== '' && $params['id'] !== '0')))) {
                    throw new \HomeDocs\Exception\InvalidParamsException("id");
                }

                $dbh = $container->get(\aportela\DatabaseWrapper\DB::class);
                if (! $dbh instanceof \aportela\DatabaseWrapper\DB) {
                    throw new \RuntimeException("Failed to create database handler from container");
                }

                // check existence
                $attachment = new \HomeDocs\Attachment(
                    array_key_exists("attachment_id", $args) && is_string($args['attachment_id']) ? $args['attachment_id'] : ""
                );
                $attachment->get($dbh);

                $share = new \HomeDocs\AttachmentShare($params['id'], 0, 0, 0, false);
                $share->get($dbh, null);
                if ($share->isEnabled() && ! $share->isExpired() && ! $share->hasExceedAccessLimit()) {
                    $share->incrementAccessCount($dbh);
                    $localStoragePath = $attachment->getLocalStoragePath();
                    if (file_exists($localStoragePath)) {
                        $partialContent = false;
                        $attachmentSize = filesize($localStoragePath);
                        if (! is_int($attachmentSize)) {
                            throw new \RuntimeException("Error getting attachment size");
                        }

                        $offset = 0;
                        $length = $attachmentSize;
                        if (isset($_SERVER['HTTP_RANGE']) && is_string($_SERVER['HTTP_RANGE'])) {
                            // if the HTTP_RANGE header is set we're dealing with partial content
                            $partialContent = true;
                            // find the requested range
                            // this might be too simplistic, apparently the client can request
                            // multiple ranges, which can become pretty complex, so ignore it for now
                            preg_match('/bytes=(\d+)-(\d+)?/', $_SERVER['HTTP_RANGE'], $matches);
                            $offset = intval($matches[1]);
                            $length = ((isset($matches[2])) ? intval($matches[2]) : $attachment->size) - $offset;
                        }

                        $f = fopen($localStoragePath, 'r');
                        if (! is_resource($f)) {
                            throw new \RuntimeException("Error opening local storage path");
                        }

                        fseek($f, $offset);
                        $data = fread($f, max(1, $length));
                        if (! is_string($data)) {
                            throw new \RuntimeException("Error reading attachment data");
                        }

                        fclose($f);
                        $response->getBody()->write($data);
                        if ($partialContent) {
                            // output the right headers for partial content
                            return $response->withStatus(206)
                                ->withHeader('Content-Type', \HomeDocs\Utils::getMimeType($attachment->name ?? ""))
                                ->withHeader('Content-Disposition', 'attachment; filename="' . basename((string) $attachment->name) . '"')
                                ->withHeader('Content-Length', (string) $length)
                                ->withHeader('Content-Range', 'bytes ' . $offset . '-' . ($offset + $length - 1) . '/' . $attachmentSize)
                                ->withHeader('Accept-Ranges', 'bytes');
                        } else {
                            return $response->withStatus(200)
                                ->withHeader('Content-Type', \HomeDocs\Utils::getMimeType($attachment->name ?? ""))
                                ->withHeader('Content-Disposition', 'attachment; filename="' . basename((string) $attachment->name) . '"')
                                ->withHeader('Content-Length', (string) $attachmentSize)
                                ->withHeader('Accept-Ranges', 'bytes');
                        }
                    } else {
                        throw new \HomeDocs\Exception\NotFoundException(array_key_exists("id", $args) && is_string($args['id']) ? $args['id'] : "");
                    }
                } else {
                    throw new \HomeDocs\Exception\AccessDeniedException("");
                }
            });

            $routeCollectorProxy->group('/user', function (RouteCollectorProxy $routeCollectorProxy) use ($container): void {
                $dbh = $container->get(\aportela\DatabaseWrapper\DB::class);
                if (! $dbh instanceof \aportela\DatabaseWrapper\DB) {
                    throw new \RuntimeException("Failed to create database handler from container");
                }

                $routeCollectorProxy->get('/profile', function (Request $request, Response $response, array $args) use ($dbh): \Psr\Http\Message\MessageInterface {
                    $user = new \HomeDocs\User(\HomeDocs\UserSession::getUserId());
                    $user->get($dbh);
                    unset($user->password);
                    unset($user->passwordHash);
                    $payload = \HomeDocs\Utils::getJSONPayload(
                        [
                            'user' => $user,
                        ]
                    );
                    $response->getBody()->write($payload);
                    return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
                });

                $routeCollectorProxy->put('/profile', function (Request $request, Response $response, array $args) use ($dbh): \Psr\Http\Message\MessageInterface {
                    $params = $request->getParsedBody();
                    if (! is_array($params)) {
                        throw new \HomeDocs\Exception\InvalidParamsException();
                    }

                    if (! (array_key_exists("email", $params) && is_string($params["email"]))) {
                        throw new \HomeDocs\Exception\InvalidParamsException("email");
                    }

                    $user = new \HomeDocs\User(\HomeDocs\UserSession::getUserId());
                    $user->get($dbh);
                    if ($params["email"] !== \HomeDocs\UserSession::getEmail()) {
                        $tmpUser = new \HomeDocs\User(
                            "",
                            $params["email"]
                        );
                        if ($tmpUser->exists($dbh)) {
                            throw new \HomeDocs\Exception\AlreadyExistsException("email");
                        }
                    }

                    $user->email = $params["email"];
                    $user->password = array_key_exists("password", $params) && is_string($params["password"]) ? $params["password"] : "";
                    $user->update($dbh);
                    unset($user->password);
                    unset($user->passwordHash);
                    $payload = \HomeDocs\Utils::getJSONPayload(
                        [
                            'user' => $user,
                        ]
                    );
                    $response->getBody()->write($payload);
                    return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
                });
            })->add(\HomeDocs\Middleware\CheckAuth::class);

            $routeCollectorProxy->group('/search', function (RouteCollectorProxy $routeCollectorProxy) use ($container): void {
                $dbh = $container->get(\aportela\DatabaseWrapper\DB::class);
                if (! $dbh instanceof \aportela\DatabaseWrapper\DB) {
                    throw new \RuntimeException("Failed to create database handler from container");
                }

                /**
                 * @param array<mixed> $params
                 */
                function getPagerFromParams(array $params): \aportela\DatabaseBrowserWrapper\Pager
                {
                    $currentPageIndex = 1;
                    $resultsPage = \HomeDocs\Settings::DEFAULT_RESULTS_PAGE;
                    if (array_key_exists("pager", $params) && is_array($params["pager"])) {
                        if (array_key_exists("currentPageIndex", $params["pager"]) && is_numeric($params["pager"]["currentPageIndex"])) {
                            $currentPageIndex = intval($params["pager"]["currentPageIndex"]);
                        }

                        if (array_key_exists("resultsPage", $params["pager"]) && is_numeric($params["pager"]["resultsPage"])) {
                            $resultsPage = intval($params["pager"]["resultsPage"]);
                        }
                    }

                    return (new \aportela\DatabaseBrowserWrapper\Pager(true, $currentPageIndex, $resultsPage));
                };

                /**
                 * @param array<mixed> $params
                 */
                function getSortFieldFromParams(array $params): string
                {
                    return (
                        array_key_exists("sort", $params)
                        && is_array($params["sort"])
                        && array_key_exists("field", $params["sort"])
                        && is_string($params["sort"]["field"])
                        ? $params["sort"]["field"]
                        : ""
                    );
                }

                /**
                 * @param array<mixed> $params
                 */
                function getSortOrderFromParams(array $params): \aportela\DatabaseBrowserWrapper\Order
                {
                    return (
                        array_key_exists("sort", $params)
                        && is_array($params["sort"])
                        && array_key_exists("order", $params["sort"])
                        && is_string($params["sort"]["order"])
                        && $params["sort"]["order"] === \aportela\DatabaseBrowserWrapper\Order::ASC->value
                        ? \aportela\DatabaseBrowserWrapper\Order::ASC
                        : \aportela\DatabaseBrowserWrapper\Order::DESC
                    );
                }

                /**
                 * @param array<mixed> $params
                 */
                function getReturnFragmentsFlagFromParams(array $params = []): bool
                {
                    return (
                        array_key_exists("returnFragments", $params) && is_bool($params["returnFragments"]) && $params["returnFragments"]
                    );
                }

                /**
                 * @param array<mixed> $params
                 */
                function getSkipCountFlagFromParams(array $params = []): bool
                {
                    return (array_key_exists("skipCount", $params) && is_bool($params["skipCount"]) && $params["skipCount"]);
                }

                // TODO: is this route required ? can be replaced only with /search/document using custom params ??
                $routeCollectorProxy->post('/recent_documents', function (Request $request, Response $response, array $args) use ($dbh): \Psr\Http\Message\MessageInterface {
                    $params = $request->getParsedBody();
                    if (! is_array($params)) {
                        throw new \HomeDocs\Exception\InvalidParamsException();
                    }

                    $payload = \HomeDocs\Utils::getJSONPayload(
                        [
                            'documents' => \HomeDocs\Document::searchRecent(
                                $dbh,
                                \HomeDocs\UserSession::getUserId() ?? "",
                                is_int($params["count"]) ? $params["count"] : \HomeDocs\Settings::DEFAULT_RESULTS_PAGE
                            ),
                        ]
                    );
                    $response->getBody()->write($payload);
                    return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
                });

                $routeCollectorProxy->post('/document', function (Request $request, Response $response, array $args) use ($dbh): \Psr\Http\Message\MessageInterface {
                    $params = $request->getParsedBody();
                    if (! is_array($params)) {
                        throw new \HomeDocs\Exception\InvalidParamsException();
                    }

                    $skipCount = getSkipCountFlagFromParams($params);
                    $browserResults = \HomeDocs\Document::browse(
                        $dbh,
                        getPagerFromParams($params),
                        new \HomeDocs\DocumentSearchFilter($params, \HomeDocs\UserSession::getUserId() ?? ""),
                        getSortFieldFromParams($params),
                        getSortOrderFromParams($params),
                        getReturnFragmentsFlagFromParams($params),
                        $skipCount,
                    );
                    $payload = \HomeDocs\Utils::getJSONPayload(
                        $skipCount ? [
                            "documents" => $browserResults->items,
                        ] : [
                            "pager" => [
                                "currentPageIndex" => $browserResults->pager->getCurrentPageIndex(),
                                "resultsPage" => $browserResults->pager->getResultsPage(),
                                "totalPages" => $browserResults->pager->getTotalPages(),
                                "totalResults" => $browserResults->pager->getTotalResults(),
                            ],
                            "documents" => $browserResults->items,
                        ]
                    );
                    $response->getBody()->write($payload);
                    return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
                });

                $routeCollectorProxy->post('/attachment_share', function (Request $request, Response $response, array $args) use ($dbh): \Psr\Http\Message\MessageInterface {
                    $params = $request->getParsedBody();
                    if (! is_array($params)) {
                        throw new \HomeDocs\Exception\InvalidParamsException();
                    }

                    $skipCount = getSkipCountFlagFromParams($params);
                    $browserResults = \HomeDocs\AttachmentShare::browse(
                        $dbh,
                        getPagerFromParams($params),
                        getSortFieldFromParams($params),
                        getSortOrderFromParams($params),
                        $skipCount
                    );
                    $payload = \HomeDocs\Utils::getJSONPayload(
                        $skipCount ? [
                            "sharedAttachments" => $browserResults->items,
                        ] : [
                            "pager" => [
                                "currentPageIndex" => $browserResults->pager->getCurrentPageIndex(),
                                "resultsPage" => $browserResults->pager->getResultsPage(),
                                "totalPages" => $browserResults->pager->getTotalPages(),
                                "totalResults" => $browserResults->pager->getTotalResults(),
                            ],
                            "sharedAttachments" => $browserResults->items,
                        ]
                    );
                    $response->getBody()->write($payload);
                    return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
                });
            })->add(\HomeDocs\Middleware\CheckAuth::class);

            $routeCollectorProxy->group('/document', function (RouteCollectorProxy $routeCollectorProxy) use ($container): void {
                $dbh = $container->get(\aportela\DatabaseWrapper\DB::class);
                if (! $dbh instanceof \aportela\DatabaseWrapper\DB) {
                    throw new \RuntimeException("Failed to create database handler from container");
                }

                $routeCollectorProxy->get('/{id}', function (Request $request, Response $response, array $args) use ($dbh): \Psr\Http\Message\MessageInterface {
                    $document = new \HomeDocs\Document();
                    $document->id = array_key_exists("id", $args) && is_string($args['id']) ? $args['id'] : null;
                    $document->get($dbh);

                    $payload = \HomeDocs\Utils::getJSONPayload(
                        [
                            'document' => $document,
                        ]
                    );
                    $response->getBody()->write($payload);
                    return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
                });

                $routeCollectorProxy->get('/{id}/notes', function (Request $request, Response $response, array $args) use ($dbh): \Psr\Http\Message\MessageInterface {
                    $document = new \HomeDocs\Document();
                    $document->id = array_key_exists("id", $args) && is_string($args['id']) ? $args['id'] : null;
                    $document->get($dbh);

                    $payload = \HomeDocs\Utils::getJSONPayload(
                        [
                            'notes' => $document->notes,
                        ]
                    );
                    $response->getBody()->write($payload);
                    return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
                });

                $routeCollectorProxy->get('/{id}/attachments', function (Request $request, Response $response, array $args) use ($dbh): \Psr\Http\Message\MessageInterface {
                    $document = new \HomeDocs\Document();
                    $document->id = array_key_exists("id", $args) && is_string($args['id']) ? $args['id'] : null;
                    $document->get($dbh);

                    $payload = \HomeDocs\Utils::getJSONPayload(
                        [
                            'attachments' => $document->attachments,
                        ]
                    );
                    $response->getBody()->write($payload);
                    return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
                });

                $routeCollectorProxy->post('/{id}', function (Request $request, Response $response, array $args) use ($dbh): \Psr\Http\Message\MessageInterface {
                    $params = $request->getParsedBody();
                    if (! is_array($params)) {
                        throw new \HomeDocs\Exception\InvalidParamsException();
                    }

                    $documentAttachments = $params["attachments"] ?? [];
                    $attachments = [];
                    if (is_array($documentAttachments) && $documentAttachments !== []) {
                        foreach ($documentAttachments as $documentAttachment) {
                            if (! is_array($documentAttachment)) {
                                throw new \HomeDocs\Exception\InvalidParamsException("attachments");
                            }

                            $attachments[] = new \HomeDocs\Attachment(
                                is_string($documentAttachment["id"]) ? $documentAttachment["id"] : "",
                                is_string($documentAttachment["name"]) ? $documentAttachment["name"] : null,
                                is_int($documentAttachment["size"]) ? $documentAttachment["size"] : null,
                                is_string($documentAttachment["hash"]) ? $documentAttachment["hash"] : null
                            );
                        }
                    }

                    $documentNotes = $params["notes"] ?? [];
                    $notes = [];
                    if (is_array($documentNotes) && $documentNotes !== []) {
                        foreach ($documentNotes as $documentNote) {
                            if (! is_array($documentNote)) {
                                throw new \HomeDocs\Exception\InvalidParamsException("notes");
                            }

                            $notes[] = new \HomeDocs\Note(
                                is_string($documentNote["id"]) ? $documentNote["id"] : null,
                                null,
                                is_string($documentNote["body"]) ? $documentNote["body"] : null
                            );
                        }
                    }

                    /**
                     * @var array<string>
                     */
                    $tags = is_array($params["tags"]) ? $params["tags"] : [];

                    $document = new \HomeDocs\Document(
                        array_key_exists("id", $args) && is_string($args['id']) ? $args['id'] : null,
                        array_key_exists("title", $params) && is_string($params["title"]) ? $params["title"] : null,
                        array_key_exists("description", $params) && is_string($params["description"]) ? $params["description"] : null,
                        null,
                        null,
                        $tags,
                        $attachments,
                        $notes,
                    );

                    $document->add($dbh);

                    $document->get($dbh);

                    $payload = \HomeDocs\Utils::getJSONPayload(
                        [
                            'document' => $document,
                        ]
                    );
                    $response->getBody()->write($payload);
                    return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
                });

                $routeCollectorProxy->put('/{id}', function (Request $request, Response $response, array $args) use ($dbh): \Psr\Http\Message\MessageInterface {
                    $params = $request->getParsedBody();
                    if (! is_array($params)) {
                        throw new \HomeDocs\Exception\InvalidParamsException();
                    }

                    $document = new \HomeDocs\Document(
                        array_key_exists("id", $args) && is_string($args['id']) ? $args['id'] : null
                    );
                    // test existence && check permissions
                    $document->get($dbh);

                    $documentAttachments = $params["attachments"] ?? [];
                    $attachments = [];
                    if (is_array($documentAttachments) && $documentAttachments !== []) {
                        foreach ($documentAttachments as $documentAttachment) {
                            if (! is_array($documentAttachment)) {
                                throw new \HomeDocs\Exception\InvalidParamsException("attachments");
                            }

                            $attachments[] = new \HomeDocs\Attachment(
                                is_string($documentAttachment["id"]) ? $documentAttachment["id"] : "",
                                is_string($documentAttachment["name"]) ? $documentAttachment["name"] : null,
                                is_int($documentAttachment["size"]) ? $documentAttachment["size"] : null,
                                is_string($documentAttachment["hash"]) ? $documentAttachment["hash"] : null
                            );
                        }
                    }

                    $documentNotes = $params["notes"] ?? [];
                    $notes = [];
                    if (is_array($documentNotes) && $documentNotes !== []) {
                        foreach ($documentNotes as $documentNote) {
                            if (! is_array($documentNote)) {
                                throw new \HomeDocs\Exception\InvalidParamsException("notes");
                            }

                            $notes[] = new \HomeDocs\Note(
                                is_string($documentNote["id"]) ? $documentNote["id"] : null,
                                null,
                                is_string($documentNote["body"]) ? $documentNote["body"] : null
                            );
                        }
                    }

                    /**
                     * @var array<string>
                     */
                    $tags = is_array($params["tags"]) ? $params["tags"] : [];

                    $document = new \HomeDocs\Document(
                        array_key_exists("id", $args) && is_string($args['id']) ? $args['id'] : null,
                        array_key_exists("title", $params) && is_string($params["title"]) ? $params["title"] : null,
                        array_key_exists("description", $params) && is_string($params["description"]) ? $params["description"] : null,
                        null,
                        null,
                        $tags,
                        $attachments,
                        $notes
                    );
                    $document->update($dbh);

                    $document->get($dbh);

                    $payload = \HomeDocs\Utils::getJSONPayload(
                        [
                            'document' => $document,
                        ]
                    );
                    $response->getBody()->write($payload);
                    return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
                });

                $routeCollectorProxy->delete('/{id}', function (Request $request, Response $response, array $args) use ($dbh): \Psr\Http\Message\MessageInterface {
                    $document = new \HomeDocs\Document(
                        array_key_exists("id", $args) && is_string($args['id']) ? $args['id'] : ""
                    );

                    // test existence && check permissions
                    $document->get($dbh);

                    $document->delete($dbh);

                    $payload = \HomeDocs\Utils::getJSONPayload(
                        []
                    );
                    $response->getBody()->write($payload);
                    return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
                });
            })->add(\HomeDocs\Middleware\CheckAuth::class);

            $routeCollectorProxy->group('/attachment', function (RouteCollectorProxy $routeCollectorProxy) use ($container): void {
                $dbh = $container->get(\aportela\DatabaseWrapper\DB::class);
                if (! $dbh instanceof \aportela\DatabaseWrapper\DB) {
                    throw new \RuntimeException("Failed to create database handler from container");
                }

                $routeCollectorProxy->post('/{attachment_id}/share', function (Request $request, Response $response, array $args) use ($dbh): \Psr\Http\Message\MessageInterface {
                    // check existence
                    $attachment = new \HomeDocs\Attachment(
                        array_key_exists("attachment_id", $args) && is_string($args['attachment_id']) ? $args['attachment_id'] : ""
                    );
                    $attachment->get($dbh);

                    $attachmentShare = new \HomeDocs\AttachmentShare("", 0, 0, 0, false);
                    $attachmentShare->add($dbh, $attachment->id);
                    $attachmentShare->get($dbh, null);

                    $payload = \HomeDocs\Utils::getJSONPayload(
                        [
                            'attachmentShare' => $attachmentShare,
                        ]
                    );
                    $response->getBody()->write($payload);
                    return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
                });

                $routeCollectorProxy->put('/{attachment_id}/share', function (Request $request, Response $response, array $args) use ($dbh): \Psr\Http\Message\MessageInterface {
                    // check existence
                    $attachment = new \HomeDocs\Attachment(
                        array_key_exists("attachment_id", $args) && is_string($args['attachment_id']) ? $args['attachment_id'] : ""
                    );
                    $params = $request->getParsedBody();
                    if (! is_array($params)) {
                        throw new \HomeDocs\Exception\InvalidParamsException();
                    }

                    if (! (array_key_exists("expiresAtTimestamp", $params) && is_numeric($params["expiresAtTimestamp"]))) {
                        throw new \HomeDocs\Exception\InvalidParamsException("expiresAtTimestamp");
                    }

                    if (! (array_key_exists("accessLimit", $params) && is_numeric($params["accessLimit"]))) {
                        throw new \HomeDocs\Exception\InvalidParamsException("accessLimit");
                    }

                    if (! (array_key_exists("enabled", $params) && is_bool($params["enabled"]))) {
                        throw new \HomeDocs\Exception\InvalidParamsException("enabled");
                    }

                    $attachment->get($dbh);
                    $attachmentShare = new \HomeDocs\AttachmentShare("", 0, intval($params["expiresAtTimestamp"]), intval($params["accessLimit"]), $params["enabled"]);
                    $attachmentShare->update($dbh, $attachment->id);
                    $attachmentShare->get($dbh, $attachment->id);

                    $payload = \HomeDocs\Utils::getJSONPayload(
                        [
                            'attachmentShare' => $attachmentShare,
                        ]
                    );
                    $response->getBody()->write($payload);
                    return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
                });

                $routeCollectorProxy->delete('/{attachment_id}/share', function (Request $request, Response $response, array $args) use ($dbh): \Psr\Http\Message\MessageInterface {
                    // check existence
                    $attachment = new \HomeDocs\Attachment(
                        array_key_exists("attachment_id", $args) && is_string($args['attachment_id']) ? $args['attachment_id'] : ""
                    );
                    $attachment->get($dbh);

                    $attachmentShare = new \HomeDocs\AttachmentShare("", 0, 0, 0, false);
                    $attachmentShare->delete($dbh, $attachment->id);

                    $payload = \HomeDocs\Utils::getJSONPayload(
                        []
                    );
                    $response->getBody()->write($payload);
                    return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
                });

                $routeCollectorProxy->get('/{attachment_id}/share', function (Request $request, Response $response, array $args) use ($dbh): \Psr\Http\Message\MessageInterface {
                    // check existence
                    $attachment = new \HomeDocs\Attachment(
                        array_key_exists("attachment_id", $args) && is_string($args['attachment_id']) ? $args['attachment_id'] : ""
                    );
                    $attachment->get($dbh);

                    $attachmentShare = new \HomeDocs\AttachmentShare("", 0, 0, 0, false);
                    $attachmentShare->get($dbh, $attachment->id);

                    $payload = \HomeDocs\Utils::getJSONPayload(
                        [
                            'attachmentShare' => $attachmentShare,
                        ]
                    );
                    $response->getBody()->write($payload);
                    return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
                });

                $routeCollectorProxy->get('/{id}[/{inline}]', function (Request $request, Response $response, array $args) use ($dbh): \Psr\Http\Message\MessageInterface {
                    $attachment = new \HomeDocs\Attachment(
                        array_key_exists("id", $args) && is_string($args['id']) ? $args['id'] : ""
                    );
                    $attachment->get($dbh);

                    $localStoragePath = $attachment->getLocalStoragePath();
                    if (file_exists($localStoragePath)) {
                        $partialContent = false;
                        $attachmentSize = filesize($localStoragePath);
                        if (! is_int($attachmentSize)) {
                            throw new \RuntimeException("Error getting attachment size");
                        }

                        $offset = 0;
                        $length = $attachmentSize;
                        if (isset($_SERVER['HTTP_RANGE']) && is_string($_SERVER['HTTP_RANGE'])) {
                            // if the HTTP_RANGE header is set we're dealing with partial content
                            $partialContent = true;
                            // find the requested range
                            // this might be too simplistic, apparently the client can request
                            // multiple ranges, which can become pretty complex, so ignore it for now
                            preg_match('/bytes=(\d+)-(\d+)?/', $_SERVER['HTTP_RANGE'], $matches);
                            $offset = intval($matches[1]);
                            $length = ((isset($matches[2])) ? intval($matches[2]) : $attachment->size) - $offset;
                        }

                        $f = fopen($localStoragePath, 'r');
                        if (! is_resource($f)) {
                            throw new \RuntimeException("Error opening local storage path");
                        }

                        fseek($f, $offset);
                        $data = fread($f, max(1, $length));
                        if (! is_string($data)) {
                            throw new \RuntimeException("Error reading attachment data");
                        }

                        fclose($f);
                        $response->getBody()->write($data);
                        if ($partialContent) {
                            // output the right headers for partial content
                            return $response->withStatus(206)
                                ->withHeader('Content-Type', \HomeDocs\Utils::getMimeType($attachment->name ?? ""))
                                ->withHeader('Content-Disposition', (isset($args['inline']) ? 'inline' : 'attachment') . '; filename="' . basename((string) $attachment->name) . '"')
                                ->withHeader('Content-Length', (string) $length)
                                ->withHeader('Content-Range', 'bytes ' . $offset . '-' . ($offset + $length - 1) . '/' . $attachmentSize)
                                ->withHeader('Accept-Ranges', 'bytes');
                        } else {
                            return $response->withStatus(200)
                                ->withHeader('Content-Type', \HomeDocs\Utils::getMimeType($attachment->name ?? ""))
                                ->withHeader('Content-Disposition', (isset($args['inline']) ? 'inline' : 'attachment') . '; filename="' . basename((string) $attachment->name) . '"')
                                ->withHeader('Content-Length', (string) $attachmentSize)
                                ->withHeader('Accept-Ranges', 'bytes');
                        }
                    } else {
                        throw new \HomeDocs\Exception\NotFoundException(array_key_exists("id", $args) && is_string($args['id']) ? $args['id'] : "");
                    }
                });

                $routeCollectorProxy->post('[/{id}]', function (Request $request, Response $response, array $args) use ($dbh): \Psr\Http\Message\MessageInterface {
                    $uploadedFiles = $request->getUploadedFiles();
                    $file = $uploadedFiles['file'] ?? null;
                    if ($file instanceof \Psr\Http\Message\UploadedFileInterface) {
                        if ($file->getError() === UPLOAD_ERR_INI_SIZE) {
                            throw new \HomeDocs\Exception\UploadException("Content too large", 413);
                        } else {
                            $attachment = new \HomeDocs\Attachment(
                                array_key_exists("id", $args) && is_string($args['id']) ? $args['id'] : \HomeDocs\Utils::uuidv4(),
                                $file->getClientFilename(),
                                $file->getSize()
                            );
                            $attachment->add($dbh, $file);
                            $payload = \HomeDocs\Utils::getJSONPayload(
                                [
                                    'data' => [
                                        "id" => $attachment->id,
                                        "name" => $attachment->name,
                                        "size" => $attachment->size,
                                        "hash" => $attachment->hash,
                                        "createdAtTimestamp" => $attachment->createdAtTimestamp,
                                    ],
                                ]
                            );
                            $response->getBody()->write($payload);
                            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
                        }
                    } else {
                        throw new \HomeDocs\Exception\InvalidParamsException("file");
                    }
                });

                $routeCollectorProxy->delete('/{id}', function (Request $request, Response $response, array $args) use ($dbh): \Psr\Http\Message\MessageInterface {
                    $attachment = new \HomeDocs\Attachment(
                        array_key_exists("id", $args) && is_string($args['id']) ? $args['id'] : ""
                    );
                    if ($attachment->isLinkedToDocument($dbh)) {
                        throw new \HomeDocs\Exception\AccessDeniedException();
                    } else {
                        $attachment->remove($dbh);
                        $payload = \HomeDocs\Utils::getJSONPayload(
                            []
                        );
                        $response->getBody()->write($payload);
                        return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
                    }
                });
            })->add(\HomeDocs\Middleware\CheckAuth::class);

            $routeCollectorProxy->get('/tag-cloud', function (Request $request, Response $response, array $args) use ($container): \Psr\Http\Message\MessageInterface {
                $dbh = $container->get(\aportela\DatabaseWrapper\DB::class);
                if (! $dbh instanceof \aportela\DatabaseWrapper\DB) {
                    throw new \RuntimeException("Failed to create database handler from container");
                }

                $payload = \HomeDocs\Utils::getJSONPayload(
                    [
                        'tags' => \HomeDocs\Tag::getCloud($dbh),
                    ]
                );
                $response->getBody()->write($payload);
                return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
            })->add(\HomeDocs\Middleware\CheckAuth::class);

            $routeCollectorProxy->get('/tags', function (Request $request, Response $response, array $args) use ($container): \Psr\Http\Message\MessageInterface {
                $dbh = $container->get(\aportela\DatabaseWrapper\DB::class);
                if (! $dbh instanceof \aportela\DatabaseWrapper\DB) {
                    throw new \RuntimeException("Failed to create database handler from container");
                }

                $payload = \HomeDocs\Utils::getJSONPayload(
                    [
                        'tags' => \HomeDocs\Tag::search($dbh),
                    ]
                );
                $response->getBody()->write($payload);
                return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
            })->add(\HomeDocs\Middleware\CheckAuth::class);

            $routeCollectorProxy->group('/stats', function (RouteCollectorProxy $routeCollectorProxy) use ($container): void {
                $dbh = $container->get(\aportela\DatabaseWrapper\DB::class);
                if (! $dbh instanceof \aportela\DatabaseWrapper\DB) {
                    throw new \RuntimeException("Failed to create database handler from container");
                }

                $routeCollectorProxy->get('/total-published-documents', function (Request $request, Response $response, array $args) use ($dbh): \Psr\Http\Message\MessageInterface {
                    $payload = \HomeDocs\Utils::getJSONPayload(
                        [
                            'count' => \HomeDocs\Stats::getTotalPublishedDocuments($dbh, strval(\HomeDocs\UserSession::getUserId())),
                        ]
                    );
                    $response->getBody()->write($payload);
                    return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
                });

                $routeCollectorProxy->get('/total-uploaded-attachments', function (Request $request, Response $response, array $args) use ($dbh): \Psr\Http\Message\MessageInterface {
                    $payload = \HomeDocs\Utils::getJSONPayload(
                        [
                            'count' => \HomeDocs\Stats::getTotalUploadedAttachments($dbh, strval(\HomeDocs\UserSession::getUserId())),
                        ]
                    );
                    $response->getBody()->write($payload);
                    return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
                });

                $routeCollectorProxy->get('/total-uploaded-attachments-disk-usage', function (Request $request, Response $response, array $args) use ($dbh): \Psr\Http\Message\MessageInterface {
                    $payload = \HomeDocs\Utils::getJSONPayload(
                        [
                            'size' => \HomeDocs\Stats::getTotalUploadedAttachmentsDiskUsage($dbh, strval(\HomeDocs\UserSession::getUserId())),
                        ]
                    );
                    $response->getBody()->write($payload);
                    return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
                });

                $routeCollectorProxy->get('/heatmap-activity-data', function (Request $request, Response $response, array $args) use ($dbh): \Psr\Http\Message\MessageInterface {
                    $queryParams = $request->getQueryParams();
                    $payload = \HomeDocs\Utils::getJSONPayload(
                        [
                            'heatmap' => \HomeDocs\Stats::getActivityHeatMapData(
                                $dbh,
                                strval(\HomeDocs\UserSession::getUserId()),
                                is_int($queryParams["fromTimestamp"]) ? $queryParams["fromTimestamp"] : 0
                            ),
                        ]
                    );
                    $response->getBody()->write($payload);
                    return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
                });
            })->add(\HomeDocs\Middleware\CheckAuth::class);
        }
    )->add(\HomeDocs\Middleware\APIExceptionCatcher::class);
};
