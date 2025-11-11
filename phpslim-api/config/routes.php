<?php

use Psr\Container\ContainerInterface;
use Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

// TODO: CheckAuth & JWT middlewares (combine ?)
return function (App $app): void {

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
    })->add(\HomeDocs\Middleware\JWT::class);

    $app->group(
        '/api3',
        function (RouteCollectorProxy $routeCollectorProxy) use ($app): void {
            $container = $app->getContainer();
            if ($container == null) {
                throw new \RuntimeException("Error getting container");
            }

            $settings = new \HomeDocs\Settings();
            $initialState = \HomeDocs\Utils::getInitialState($settings);

            $routeCollectorProxy->get('/initial_state', function (Request $request, Response $response, array $args) use ($initialState) {
                $payload = \HomeDocs\Utils::getJSONPayload(
                    [
                        'initialState' => $initialState
                    ]
                );
                $response->getBody()->write($payload);
                return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
            });

            $routeCollectorProxy->group('/auth', function (RouteCollectorProxy $routeCollectorProxy) use ($container, $initialState, $settings): void {
                $routeCollectorProxy->post('/register', function (Request $request, Response $response, array $args) use ($container, $initialState, $settings) {
                    if ($settings->allowSignUp()) {
                        $params = $request->getParsedBody();
                        if (! is_array($params)) {
                            throw new \HomeDocs\Exception\InvalidParamsException();
                        }

                        $dbh = $container->get(\aportela\DatabaseWrapper\DB::class);
                        if (! $dbh instanceof \aportela\DatabaseWrapper\DB) {
                            throw new \RuntimeException("Failed to create database handler from container");
                        }

                        if (\HomeDocs\User::isEmailUsed($dbh, is_string($params["email"]) ? $params["email"] : "")) {
                            throw new \HomeDocs\Exception\AlreadyExistsException("email");
                        } else {
                            $user = new \HomeDocs\User(
                                is_string($params["id"]) ? $params["id"] : "",
                                is_string($params["email"]) ? $params["email"] : "",
                                is_string($params["password"]) ? $params["password"] : ""
                            );
                            $user->add($dbh);
                            $payload = \HomeDocs\Utils::getJSONPayload(
                                [
                                    'initialState' => $initialState
                                ]
                            );
                            $response->getBody()->write($payload);
                            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
                        }
                    } else {
                        throw new \HomeDocs\Exception\AccessDeniedException("");
                    }
                });

                $routeCollectorProxy->post('/login', function (Request $request, Response $response, array $args) use ($container, $initialState) {
                    $params = $request->getParsedBody();
                    if (! is_array($params)) {
                        throw new \HomeDocs\Exception\InvalidParamsException();
                    }

                    $dbh = $container->get(\aportela\DatabaseWrapper\DB::class);
                    if (! $dbh instanceof \aportela\DatabaseWrapper\DB) {
                        throw new \RuntimeException("Failed to create database handler from container");
                    }

                    $user = new \HomeDocs\User(
                        "",
                        is_string($params["email"]) ? $params["email"] : "",
                        is_string($params["password"]) ? $params["password"] : ""
                    );
                    $user->login($dbh);

                    $payload = \HomeDocs\Utils::getJSONPayload(
                        [
                            'initialState' => $initialState
                        ]
                    );
                    $response->getBody()->write($payload);
                    return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
                });

                $routeCollectorProxy->post('/logout', function (Request $request, Response $response, array $args) use ($initialState) {
                    \HomeDocs\User::logout();
                    $payload = \HomeDocs\Utils::getJSONPayload(
                        [
                            'initialState' => $initialState
                        ]
                    );
                    $response->getBody()->write($payload);
                    return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
                });
            });

            $routeCollectorProxy->group('/user', function (RouteCollectorProxy $routeCollectorProxy) use ($container, $initialState): void {
                $dbh = $container->get(\aportela\DatabaseWrapper\DB::class);
                if (! $dbh instanceof \aportela\DatabaseWrapper\DB) {
                    throw new \RuntimeException("Failed to create database handler from container");
                }

                $routeCollectorProxy->get('/profile', function (Request $request, Response $response, array $args) use ($dbh, $initialState) {
                    $user = new \HomeDocs\User(\HomeDocs\UserSession::getUserId());
                    $user->get($dbh);
                    unset($user->password);
                    unset($user->passwordHash);
                    $payload = \HomeDocs\Utils::getJSONPayload(
                        [
                            'initialState' => $initialState,
                            'data' => $user
                        ]
                    );
                    $response->getBody()->write($payload);
                    return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
                });

                $routeCollectorProxy->put('/profile', function (Request $request, Response $response, array $args) use ($dbh, $initialState) {
                    $params = $request->getParsedBody();
                    if (! is_array($params)) {
                        throw new \HomeDocs\Exception\InvalidParamsException();
                    }

                    if (! is_string($params["email"])) {
                        throw new \HomeDocs\Exception\InvalidParamsException("email");
                    }

                    $user = new \HomeDocs\User(\HomeDocs\UserSession::getUserId());
                    $user->get($dbh);
                    if ($params["email"] != \HomeDocs\UserSession::getEmail()) {
                        $tmpUser = new \HomeDocs\User(
                            "",
                            $params["email"]
                        );
                        if ($tmpUser->exists($dbh)) {
                            throw new \HomeDocs\Exception\AlreadyExistsException("email");
                        }
                    }

                    $user->email = $params["email"];
                    $user->password = is_string($params["password"]) ? $params["password"] : "";
                    $user->update($dbh);
                    unset($user->password);
                    unset($user->passwordHash);
                    $payload = \HomeDocs\Utils::getJSONPayload(
                        [
                            'initialState' => $initialState,
                            'data' => $user
                        ]
                    );
                    $response->getBody()->write($payload);
                    return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
                });
            })->add(\HomeDocs\Middleware\CheckAuth::class);

            $routeCollectorProxy->group('/search', function (RouteCollectorProxy $routeCollectorProxy) use ($container, $initialState): void {
                $dbh = $container->get(\aportela\DatabaseWrapper\DB::class);
                if (! $dbh instanceof \aportela\DatabaseWrapper\DB) {
                    throw new \RuntimeException("Failed to create database handler from container");
                }

                // TODO: is this required ? can be recplaced only with /search/document with custom params
                $routeCollectorProxy->post('/recent_documents', function (Request $request, Response $response, array $args) use ($dbh, $initialState) {
                    $params = $request->getParsedBody();
                    if (! is_array($params)) {
                        throw new \HomeDocs\Exception\InvalidParamsException();
                    }
                    $payload = \HomeDocs\Utils::getJSONPayload(
                        [
                            'initialState' => $initialState,
                            'recentDocuments' => \HomeDocs\Document::searchRecent(
                                $dbh,
                                is_int($params["count"]) ? $params["count"] : \HomeDocs\Settings::DEFAULT_RESULTS_PAGE
                            )
                        ]
                    );
                    $response->getBody()->write($payload);
                    return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
                });

                $routeCollectorProxy->post('/document', function (Request $request, Response $response, array $args) use ($dbh, $initialState) {
                    $params = $request->getParsedBody();
                    if (! is_array($params)) {
                        throw new \HomeDocs\Exception\InvalidParamsException();
                    }

                    $payload = \HomeDocs\Utils::getJSONPayload(
                        [
                            'initialState' => $initialState,
                            'results' => \HomeDocs\Document::search(
                                $dbh,
                                new \aportela\DatabaseBrowserWrapper\Pager(
                                    true,
                                    is_int($params["currentPage"]) ? $params["currentPage"] : 1,
                                    is_int($params["resultsPage"]) ? $params["resultsPage"] : \HomeDocs\Settings::DEFAULT_RESULTS_PAGE
                                ),
                                [
                                    "title" => $params["title"] ?? null,
                                    "description" => $params["description"] ?? null,
                                    "notesBody" => $params["notesBody"] ?? null,
                                    "attachmentsFilename" => $params["attachmentsFilename"] ?? null,
                                    "fromCreationTimestampCondition" => is_int($params["fromCreationTimestampCondition"]) ? $params["fromCreationTimestampCondition"] : 0,
                                    "toCreationTimestampCondition" => is_int($params["toCreationTimestampCondition"]) ? $params["toCreationTimestampCondition"] : 0,
                                    "fromLastUpdateTimestampCondition" => is_int($params["fromLastUpdateTimestampCondition"]) ? $params["fromLastUpdateTimestampCondition"] : 0,
                                    "toLastUpdateTimestampCondition" => is_int($params["toLastUpdateTimestampCondition"]) ? $params["toLastUpdateTimestampCondition"] : 0,
                                    "fromUpdatedOnTimestampCondition" => is_int($params["fromUpdatedOnTimestampCondition"]) ? $params["fromUpdatedOnTimestampCondition"] : 0,
                                    "toUpdatedOnTimestampCondition" => is_int($params["toUpdatedOnTimestampCondition"]) ? $params["toUpdatedOnTimestampCondition"] : 0,
                                    "tags" => is_array($params["tags"]) ? $params["tags"] : [],
                                ],
                                is_string($params["sortBy"]) ? $params["sortBy"] : "",
                                is_string($params["sortOrder"]) && $params["sortOrder"] == "ASC" ? \aportela\DatabaseBrowserWrapper\Order::ASC : \aportela\DatabaseBrowserWrapper\Order::DESC
                            )
                        ]
                    );
                    $response->getBody()->write($payload);
                    return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
                });
            })->add(\HomeDocs\Middleware\CheckAuth::class);

            $routeCollectorProxy->group('/document', function (RouteCollectorProxy $routeCollectorProxy) use ($container, $initialState): void {
                $dbh = $container->get(\aportela\DatabaseWrapper\DB::class);
                if (! $dbh instanceof \aportela\DatabaseWrapper\DB) {
                    throw new \RuntimeException("Failed to create database handler from container");
                }

                $routeCollectorProxy->get('/{id}', function (Request $request, Response $response, array $args) use ($dbh, $initialState) {
                    $document = new \HomeDocs\Document();
                    $document->id = is_string($args['id']) ? $args['id'] : null;
                    $document->get($dbh);

                    $payload = \HomeDocs\Utils::getJSONPayload(
                        [
                            'initialState' => $initialState,
                            'document' => $document
                        ]
                    );
                    $response->getBody()->write($payload);
                    return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
                });

                $routeCollectorProxy->get('/{id}/notes', function (Request $request, Response $response, array $args) use ($dbh, $initialState) {
                    $document = new \HomeDocs\Document();
                    $document->id = is_string($args['id']) ? $args['id'] : null;
                    $document->get($dbh);

                    $payload = \HomeDocs\Utils::getJSONPayload(
                        [
                            'initialState' => $initialState,
                            'notes' => $document->notes
                        ]
                    );
                    $response->getBody()->write($payload);
                    return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
                });

                $routeCollectorProxy->get('/{id}/attachments', function (Request $request, Response $response, array $args) use ($dbh, $initialState) {
                    $document = new \HomeDocs\Document();
                    $document->id = is_string($args['id']) ? $args['id'] : null;
                    $document->get($dbh);

                    $payload = \HomeDocs\Utils::getJSONPayload(
                        [
                            'initialState' => $initialState,
                            'attachments' => $document->attachments
                        ]
                    );
                    $response->getBody()->write($payload);
                    return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
                });

                $routeCollectorProxy->post('/{id}', function (Request $request, Response $response, array $args) use ($dbh, $initialState) {
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
                                is_int($documentNote["createdOnTimestamp"]) ?  $documentNote["createdOnTimestamp"] : null,
                                is_string($documentNote["body"]) ? $documentNote["body"] : null
                            );
                        }
                    }

                    $document = new \HomeDocs\Document(
                        $args['id'] ?? "",
                        $params["title"] ?? null,
                        $params["description"] ?? null,
                        null,
                        null,
                        $params["tags"] ?? [],
                        $attachments,
                        $notes,
                    );

                    $dbh->beginTransaction();
                    try {
                        $document->add($dbh);
                        $dbh->commit();
                    } catch (\aportela\DatabaseWrapper\Exception\DBException $dBException) {
                        $dbh->rollBack();
                        throw $dBException;
                    }

                    $document->get($dbh);

                    $payload = \HomeDocs\Utils::getJSONPayload(
                        [
                            'initialState' => $initialState,
                            'document' => $document
                        ]
                    );
                    $response->getBody()->write($payload);
                    return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
                });

                $routeCollectorProxy->put('/{id}', function (Request $request, Response $response, array $args) use ($container, $dbh, $initialState) {
                    $params = $request->getParsedBody();
                    if (! is_array($params)) {
                        throw new \HomeDocs\Exception\InvalidParamsException();
                    }

                    $document = new \HomeDocs\Document(
                        $args['id'] ?? ""
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
                                is_int($documentNote["createdOnTimestamp"]) ? $documentNote["createdOnTimestamp"] : null,
                                is_string($documentNote["body"]) ? $documentNote["body"] : null
                            );
                        }
                    }

                    $document = new \HomeDocs\Document(
                        $args['id'] ?? "",
                        $params["title"] ?? null,
                        $params["description"] ?? null,
                        null,
                        null,
                        $params["tags"] ?? [],
                        $attachments,
                        $notes
                    );
                    try {
                        $dbh->beginTransaction();
                        $document->update($dbh);
                        $dbh->commit();
                    } catch (\aportela\DatabaseWrapper\Exception\DBException $dBException) {
                        $dbh->rollBack();
                        throw $dBException;
                    }

                    $dbh = $container->get(\aportela\DatabaseWrapper\DB::class);
                    if (! $dbh instanceof \aportela\DatabaseWrapper\DB) {
                        throw new \RuntimeException("Failed to create database handler from container");
                    }

                    $document->get($dbh);
                    $payload = \HomeDocs\Utils::getJSONPayload(
                        [
                            'initialState' => $initialState,
                            'document' => $document
                        ]
                    );
                    $response->getBody()->write($payload);
                    return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
                });

                $routeCollectorProxy->delete('/{id}', function (Request $request, Response $response, array $args) use ($dbh, $initialState) {
                    $document = new \HomeDocs\Document(
                        $args['id'] ?? ""
                    );

                    // test existence && check permissions
                    $document->get($dbh);
                    try {
                        $dbh->beginTransaction();
                        $document->delete($dbh);
                        $dbh->commit();
                    } catch (\aportela\DatabaseWrapper\Exception\DBException $dBException) {
                        $dbh->rollBack();
                        throw $dBException;
                    }

                    $payload = \HomeDocs\Utils::getJSONPayload(
                        [
                            'initialState' => $initialState
                        ]
                    );
                    $response->getBody()->write($payload);
                    return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
                });
            })->add(\HomeDocs\Middleware\CheckAuth::class);

            $routeCollectorProxy->group('/attachment', function (RouteCollectorProxy $routeCollectorProxy) use ($container, $initialState): void {
                $dbh = $container->get(\aportela\DatabaseWrapper\DB::class);
                if (! $dbh instanceof \aportela\DatabaseWrapper\DB) {
                    throw new \RuntimeException("Failed to create database handler from container");
                }

                $routeCollectorProxy->get('/{id}[/{inline}]', function (Request $request, Response $response, array $args) use ($dbh): \Psr\Http\Message\MessageInterface {
                    $attachment = new \HomeDocs\Attachment(
                        $args['id'] ?? ""
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
                        throw new \HomeDocs\Exception\NotFoundException($args['id'] ?? "");
                    }
                });

                $routeCollectorProxy->post('[/{id}]', function (Request $request, Response $response, array $args) use ($dbh, $initialState) {
                    $uploadedFiles = $request->getUploadedFiles();
                    $file = $uploadedFiles['file'] ?? null;
                    if ($file) {
                        if ($file->getError() === UPLOAD_ERR_INI_SIZE) {
                            throw new \HomeDocs\Exception\UploadException("Content too large", 413);
                        } else {
                            $attachment = new \HomeDocs\Attachment(
                                $args['id'] ?? \HomeDocs\Utils::uuidv4(),
                                $uploadedFiles["file"]->getClientFilename(),
                                $uploadedFiles["file"]->getSize()
                            );
                            $attachment->add($dbh, $uploadedFiles["file"]);
                            $payload = \HomeDocs\Utils::getJSONPayload(
                                [
                                    'initialState' => $initialState,
                                    'data' => [
                                        "id" => $attachment->id,
                                        "name" => $attachment->name,
                                        "size" => $attachment->size,
                                        "hash" => $attachment->hash,
                                        "createdOnTimestamp" => $attachment->createdOnTimestamp
                                    ]
                                ]
                            );
                            $response->getBody()->write($payload);
                            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
                        }
                    } else {
                        throw new \HomeDocs\Exception\InvalidParamsException("file");
                    }
                });

                $routeCollectorProxy->delete('/{id}', function (Request $request, Response $response, array $args) use ($dbh, $initialState) {
                    $attachment = new \HomeDocs\Attachment(
                        $args['id'] ?? ""
                    );
                    if ($attachment->isLinkedToDocument($dbh)) {
                        throw new \HomeDocs\Exception\AccessDeniedException();
                    } else {
                        $attachment->remove($dbh);
                        $payload = \HomeDocs\Utils::getJSONPayload(
                            [
                                'initialState' => $initialState,
                            ]
                        );
                        $response->getBody()->write($payload);
                        return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
                    }
                });
            })->add(\HomeDocs\Middleware\CheckAuth::class);

            $routeCollectorProxy->get('/tag-cloud', function (Request $request, Response $response, array $args) use ($container, $initialState) {
                $dbh = $container->get(\aportela\DatabaseWrapper\DB::class);
                if (! $dbh instanceof \aportela\DatabaseWrapper\DB) {
                    throw new \RuntimeException("Failed to create database handler from container");
                }

                $payload = \HomeDocs\Utils::getJSONPayload(
                    [
                        'initialState' => $initialState,
                        'tags' => \HomeDocs\Tag::getCloud($dbh)
                    ]
                );
                $response->getBody()->write($payload);
                return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
            })->add(\HomeDocs\Middleware\CheckAuth::class);

            $routeCollectorProxy->get('/tags', function (Request $request, Response $response, array $args) use ($container, $initialState) {
                $dbh = $container->get(\aportela\DatabaseWrapper\DB::class);
                if (! $dbh instanceof \aportela\DatabaseWrapper\DB) {
                    throw new \RuntimeException("Failed to create database handler from container");
                }

                $payload = \HomeDocs\Utils::getJSONPayload(
                    [
                        'initialState' => $initialState,
                        'tags' => \HomeDocs\Tag::search($dbh)
                    ]
                );
                $response->getBody()->write($payload);
                return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
            })->add(\HomeDocs\Middleware\CheckAuth::class);

            $routeCollectorProxy->group('/stats', function (RouteCollectorProxy $routeCollectorProxy) use ($container, $initialState): void {
                $dbh = $container->get(\aportela\DatabaseWrapper\DB::class);
                if (! $dbh instanceof \aportela\DatabaseWrapper\DB) {
                    throw new \RuntimeException("Failed to create database handler from container");
                }

                $routeCollectorProxy->get('/total-published-documents', function (Request $request, Response $response, array $args) use ($initialState, $dbh) {
                    $payload = \HomeDocs\Utils::getJSONPayload(
                        [
                            'initialState' => $initialState,
                            'count' => \HomeDocs\Stats::getTotalPublishedDocuments($dbh)
                        ]
                    );
                    $response->getBody()->write($payload);
                    return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
                });

                $routeCollectorProxy->get('/total-uploaded-attachments', function (Request $request, Response $response, array $args) use ($initialState, $dbh) {
                    $payload = \HomeDocs\Utils::getJSONPayload(
                        [
                            'initialState' => $initialState,
                            'count' => \HomeDocs\Stats::getTotalUploadedAttachments($dbh)
                        ]
                    );
                    $response->getBody()->write($payload);
                    return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
                });

                $routeCollectorProxy->get('/total-uploaded-attachments-disk-usage', function (Request $request, Response $response, array $args) use ($initialState, $dbh) {
                    $payload = \HomeDocs\Utils::getJSONPayload(
                        [
                            'initialState' => $initialState,
                            'size' => \HomeDocs\Stats::getTotalUploadedAttachmentsDiskUsage($dbh)
                        ]
                    );
                    $response->getBody()->write($payload);
                    return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
                });

                $routeCollectorProxy->get('/heatmap-activity-data', function (Request $request, Response $response, array $args) use ($initialState, $dbh) {
                    $queryParams = $request->getQueryParams();
                    $payload = \HomeDocs\Utils::getJSONPayload(
                        [
                            'initialState' => $initialState,
                            'heatmap' => \HomeDocs\Stats::getActivityHeatMapData(
                                $dbh,
                                is_int($queryParams["fromTimestamp"]) ? $queryParams["fromTimestamp"] : 0
                            )
                        ]
                    );
                    $response->getBody()->write($payload);
                    return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
                });
            })->add(\HomeDocs\Middleware\CheckAuth::class);
        }
    )->add(\HomeDocs\Middleware\JWT::class)->add(\HomeDocs\Middleware\APIExceptionCatcher::class);
};
