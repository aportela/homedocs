<?php

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

            $initialState = \HomeDocs\Utils::getInitialState($app->getContainer());

            $routeCollectorProxy->get('/initial_state', function (Request $request, Response $response, array $args) use ($initialState) {
                $payload = json_encode(
                    [
                        'initialState' => $initialState
                    ]
                );
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \HomeDocs\Exception\JSONSerializerException(json_last_error_msg());
                }

                $response->getBody()->write(is_string($payload) ? $payload : "{}");
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            });

            $routeCollectorProxy->group('/auth', function (RouteCollectorProxy $routeCollectorProxy) use ($app, $initialState): void {
                $routeCollectorProxy->post('/register', function (Request $request, Response $response, array $args) use ($app, $initialState) {
                    $settings = $app->getContainer()->get('settings');
                    if ($settings['common']['allowSignUp']) {
                        $params = $request->getParsedBody();
                        $dbh = $app->getContainer()->get(\aportela\DatabaseWrapper\DB::class);
                        if (\HomeDocs\User::isEmailUsed($dbh, $params["email"] ?? "")) {
                            throw new \HomeDocs\Exception\AlreadyExistsException("email");
                        } else {
                            $user = new \HomeDocs\User(
                                $params["id"] ?? "",
                                $params["email"] ?? "",
                                $params["password"] ?? ""
                            );
                            $user->add($dbh);
                            $payload = json_encode(
                                [
                                    'initialState' => $initialState
                                ]
                            );
                            if (json_last_error() !== JSON_ERROR_NONE) {
                                throw new \HomeDocs\Exception\JSONSerializerException(json_last_error_msg());
                            }

                            $response->getBody()->write($payload);
                            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                        }
                    } else {
                        throw new \HomeDocs\Exception\AccessDeniedException("");
                    }
                });

                $routeCollectorProxy->post('/login', function (Request $request, Response $response, array $args) use ($app, $initialState) {
                    $params = $request->getParsedBody();
                    $dbh = $app->getContainer()->get(\aportela\DatabaseWrapper\DB::class);
                    $user = new \HomeDocs\User(
                        "",
                        $params["email"] ?? "",
                        $params["password"] ?? ""
                    );
                    $user->login($dbh);

                    $payload = json_encode(
                        [
                            'initialState' => $initialState
                        ]
                    );
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new \HomeDocs\Exception\JSONSerializerException(json_last_error_msg());
                    }

                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });

                $routeCollectorProxy->post('/logout', function (Request $request, Response $response, array $args) use ($initialState) {
                    \HomeDocs\User::logout();
                    $payload = json_encode(
                        [
                            'initialState' => $initialState
                        ]
                    );
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new \HomeDocs\Exception\JSONSerializerException(json_last_error_msg());
                    }

                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });
            });

            $routeCollectorProxy->group('/user', function (RouteCollectorProxy $routeCollectorProxy) use ($app, $initialState): void {
                $routeCollectorProxy->get('/profile', function (Request $request, Response $response, array $args) use ($app, $initialState) {
                    $user = new \HomeDocs\User(\HomeDocs\UserSession::getUserId());
                    $user->get($app->getContainer()->get(\aportela\DatabaseWrapper\DB::class));
                    unset($user->password);
                    unset($user->passwordHash);
                    $payload = json_encode(
                        [
                            'initialState' => $initialState,
                            'data' => $user
                        ]
                    );
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new \HomeDocs\Exception\JSONSerializerException(json_last_error_msg());
                    }

                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });

                $routeCollectorProxy->put('/profile', function (Request $request, Response $response, array $args) use ($app, $initialState) {
                    $params = $request->getParsedBody();
                    $dbh = $app->getContainer()->get(\aportela\DatabaseWrapper\DB::class);
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

                    $user->email = $params["email"] ?? "";
                    $user->password = $params["password"] ?? "";
                    $user->update($dbh);
                    unset($user->password);
                    unset($user->passwordHash);
                    $payload = json_encode(
                        [
                            'initialState' => $initialState,
                            'data' => $user
                        ]
                    );
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new \HomeDocs\Exception\JSONSerializerException(json_last_error_msg());
                    }

                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });
            })->add(\HomeDocs\Middleware\CheckAuth::class);

            $routeCollectorProxy->group('/search', function (RouteCollectorProxy $routeCollectorProxy) use ($app, $initialState): void {
                // TODO: is this required ? can be recplaced only with /search/document with custom params
                $routeCollectorProxy->post('/recent_documents', function (Request $request, Response $response, array $args) use ($app, $initialState) {
                    $settings = $app->getContainer()->get('settings');
                    $params = $request->getParsedBody();
                    $payload = json_encode(
                        [
                            'initialState' => $initialState,
                            'recentDocuments' => \HomeDocs\Document::searchRecent(
                                $app->getContainer()->get(\aportela\DatabaseWrapper\DB::class),
                                $params["count"] ?? $settings["common"]["defaultResultsPage"]
                            )
                        ]
                    );
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new \HomeDocs\Exception\JSONSerializerException(json_last_error_msg());
                    }

                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });

                $routeCollectorProxy->post('/document', function (Request $request, Response $response, array $args) use ($app, $initialState) {
                    $settings = $app->getContainer()->get('settings');
                    $params = $request->getParsedBody();
                    $payload = json_encode(
                        [
                            'initialState' => $initialState,
                            'results' => \HomeDocs\Document::search(
                                $app->getContainer()->get(\aportela\DatabaseWrapper\DB::class),
                                new \aportela\DatabaseBrowserWrapper\Pager(true, intval($params["currentPage"] ?? 1), intval($params["resultsPage"] ?? $settings["common"]["defaultResultsPage"])),
                                [
                                    "title" => $params["title"] ?? null,
                                    "description" => $params["description"] ?? null,
                                    "notesBody" => $params["notesBody"] ?? null,
                                    "attachmentsFilename" => $params["attachmentsFilename"] ?? null,
                                    "fromCreationTimestampCondition" => intval($params["fromCreationTimestampCondition"] ?? 0),
                                    "toCreationTimestampCondition" => intval($params["toCreationTimestampCondition"] ?? 0),
                                    "fromLastUpdateTimestampCondition" => intval($params["fromLastUpdateTimestampCondition"] ?? 0),
                                    "toLastUpdateTimestampCondition" => intval($params["toLastUpdateTimestampCondition"] ?? 0),
                                    "fromUpdatedOnTimestampCondition" => intval($params["fromUpdatedOnTimestampCondition"] ?? 0),
                                    "toUpdatedOnTimestampCondition" => intval($params["toUpdatedOnTimestampCondition"] ?? 0),
                                    "tags" => $params["tags"] ?? [],
                                ],
                                $params["sortBy"] ?? null,
                                $params["sortOrder"] == "ASC" ? \aportela\DatabaseBrowserWrapper\Order::ASC : \aportela\DatabaseBrowserWrapper\Order::DESC
                            )
                        ]
                    );
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new \HomeDocs\Exception\JSONSerializerException(json_last_error_msg());
                    }

                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });
            })->add(\HomeDocs\Middleware\CheckAuth::class);

            $routeCollectorProxy->group('/document', function (RouteCollectorProxy $routeCollectorProxy) use ($app, $initialState): void {
                $routeCollectorProxy->get('/{id}', function (Request $request, Response $response, array $args) use ($app, $initialState) {
                    $document = new \HomeDocs\Document();
                    $document->id = $args['id'];
                    $document->setRootStoragePath($app->getContainer()->get('settings')['paths']['storage']);
                    $document->get($app->getContainer()->get(\aportela\DatabaseWrapper\DB::class));

                    $payload = json_encode(
                        [
                            'initialState' => $initialState,
                            'document' => $document
                        ]
                    );
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new \HomeDocs\Exception\JSONSerializerException(json_last_error_msg());
                    }

                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });

                $routeCollectorProxy->get('/{id}/notes', function (Request $request, Response $response, array $args) use ($app, $initialState) {
                    $document = new \HomeDocs\Document();
                    $document->id = $args['id'];
                    $document->setRootStoragePath($app->getContainer()->get('settings')['paths']['storage']);
                    $document->get($app->getContainer()->get(\aportela\DatabaseWrapper\DB::class));

                    $payload = json_encode(
                        [
                            'initialState' => $initialState,
                            'notes' => $document->notes
                        ]
                    );
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new \HomeDocs\Exception\JSONSerializerException(json_last_error_msg());
                    }

                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });

                $routeCollectorProxy->get('/{id}/attachments', function (Request $request, Response $response, array $args) use ($app, $initialState) {
                    $document = new \HomeDocs\Document();
                    $document->id = $args['id'];
                    $document->setRootStoragePath($app->getContainer()->get('settings')['paths']['storage']);
                    $document->get($app->getContainer()->get(\aportela\DatabaseWrapper\DB::class));

                    $payload = json_encode(
                        [
                            'initialState' => $initialState,
                            'attachments' => $document->attachments
                        ]
                    );
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new \HomeDocs\Exception\JSONSerializerException(json_last_error_msg());
                    }

                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });

                $routeCollectorProxy->post('/{id}', function (Request $request, Response $response, array $args) use ($app, $initialState) {
                    $params = $request->getParsedBody();
                    $documentAttachments = $params["attachments"] ?? [];
                    $rootStoragePath = $app->getContainer()->get('settings')['paths']['storage'];
                    $attachments = [];
                    if (is_array($documentAttachments) && $documentAttachments !== []) {
                        foreach ($documentAttachments as $documentAttachment) {
                            $attachments[] = new \HomeDocs\Attachment(
                                $rootStoragePath,
                                $documentAttachment["id"],
                                $documentAttachment["name"],
                                $documentAttachment["size"],
                                $documentAttachment["hash"] ?? ""
                            );
                        }
                    }

                    $documentNotes = $params["notes"] ?? [];
                    $notes = [];
                    if (is_array($documentNotes) && $documentNotes !== []) {
                        foreach ($documentNotes as $documentNote) {
                            $notes[] = new \HomeDocs\Note(
                                $documentNote["id"],
                                $documentNote["createdOnTimestamp"],
                                $documentNote["body"]
                            );
                        }
                    }

                    $rootStoragePath = $app->getContainer()->get('settings')['paths']['storage'];
                    $document = new \HomeDocs\Document(
                        $args['id'],
                        $params["title"] ?? "",
                        $params["description"] ?? "",
                        null,
                        null,
                        $params["tags"] ?? [],
                        $attachments,
                        $notes,
                    );
                    $document->setRootStoragePath($rootStoragePath);

                    $dbh = $app->getContainer()->get(\aportela\DatabaseWrapper\DB::class);
                    $dbh->beginTransaction();
                    try {
                        $document->add($dbh);
                        $dbh->commit();
                    } catch (\aportela\DatabaseWrapper\Exception\DBException $dBException) {
                        $dbh->rollBack();
                        throw $dBException;
                    }

                    $document->setRootStoragePath($rootStoragePath);
                    $document->get($dbh);

                    $payload = json_encode(
                        [
                            'initialState' => $initialState,
                            'document' => $document
                        ]
                    );
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new \HomeDocs\Exception\JSONSerializerException(json_last_error_msg());
                    }

                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });

                $routeCollectorProxy->put('/{id}', function (Request $request, Response $response, array $args) use ($app, $initialState) {
                    $params = $request->getParsedBody();
                    $dbh = $app->getContainer()->get(\aportela\DatabaseWrapper\DB::class);
                    $document = new \HomeDocs\Document(
                        $args['id']
                    );
                    // test existence && check permissions
                    $rootStoragePath = $app->getContainer()->get('settings')['paths']['storage'];
                    $document->setRootStoragePath($rootStoragePath);
                    $document->get($dbh);

                    $documentAttachments = $params["attachments"] ?? [];
                    $attachments = [];
                    if (is_array($documentAttachments) && $documentAttachments !== []) {
                        foreach ($documentAttachments as $documentAttachment) {
                            $attachments[] = new \HomeDocs\Attachment(
                                $rootStoragePath,
                                $documentAttachment["id"],
                                $documentAttachment["name"],
                                $documentAttachment["size"],
                                $documentAttachment["hash"] ?? ""
                            );
                        }
                    }

                    $documentNotes = $params["notes"] ?? [];
                    $notes = [];
                    if (is_array($documentNotes) && $documentNotes !== []) {
                        foreach ($documentNotes as $documentNote) {
                            $notes[] = new \HomeDocs\Note(
                                $documentNote["id"],
                                $documentNote["createdOnTimestamp"],
                                $documentNote["body"]
                            );
                        }
                    }

                    $document = new \HomeDocs\Document(
                        $args['id'],
                        $params["title"] ?? "",
                        $params["description"] ?? "",
                        null,
                        null,
                        $params["tags"] ?? [],
                        $attachments,
                        $notes
                    );
                    $document->setRootStoragePath($rootStoragePath);
                    try {
                        $dbh->beginTransaction();
                        $document->update($dbh);
                        $dbh->commit();
                    } catch (\aportela\DatabaseWrapper\Exception\DBException $dBException) {
                        $dbh->rollBack();
                        throw $dBException;
                    }

                    $document->get($app->getContainer()->get(\aportela\DatabaseWrapper\DB::class));
                    $payload = json_encode(
                        [
                            'initialState' => $initialState,
                            'document' => $document
                        ]
                    );
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new \HomeDocs\Exception\JSONSerializerException(json_last_error_msg());
                    }

                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });

                $routeCollectorProxy->delete('/{id}', function (Request $request, Response $response, array $args) use ($app, $initialState) {
                    $document = new \HomeDocs\Document(
                        $args['id']
                    );
                    $document->setRootStoragePath($app->getContainer()->get('settings')['paths']['storage']);

                    $dbh = $app->getContainer()->get(\aportela\DatabaseWrapper\DB::class);
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

                    $payload = json_encode(
                        [
                            'initialState' => $initialState
                        ]
                    );
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new \HomeDocs\Exception\JSONSerializerException(json_last_error_msg());
                    }

                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });
            })->add(\HomeDocs\Middleware\CheckAuth::class);

            $routeCollectorProxy->group('/attachment', function (RouteCollectorProxy $routeCollectorProxy) use ($app, $initialState): void {
                $routeCollectorProxy->get('/{id}[/{inline}]', function (Request $request, Response $response, array $args) use ($app): \Psr\Http\Message\MessageInterface {
                    $attachment = new \HomeDocs\Attachment(
                        $app->getContainer()->get('settings')['paths']['storage'],
                        $args['id']
                    );
                    $attachment->get($app->getContainer()->get(\aportela\DatabaseWrapper\DB::class));

                    $localStoragePath = $attachment->getLocalStoragePath();
                    if (file_exists($localStoragePath)) {
                        $partialContent = false;
                        $attachmentSize = filesize($localStoragePath);
                        $offset = 0;
                        $length = $attachmentSize;
                        if (isset($_SERVER['HTTP_RANGE'])) {
                            // if the HTTP_RANGE header is set we're dealing with partial content
                            $partialContent = true;
                            // find the requested range
                            // this might be too simplistic, apparently the client can request
                            // multiple ranges, which can become pretty complex, so ignore it for now
                            preg_match('/bytes=(\d+)-(\d+)?/', (string) $_SERVER['HTTP_RANGE'], $matches);
                            $offset = intval($matches[1]);
                            $length = ((isset($matches[2])) ? intval($matches[2]) : $attachment->size) - $offset;
                        }

                        $f = fopen($localStoragePath, 'r');
                        fseek($f, $offset);
                        $data = fread($f, $length);
                        fclose($f);
                        $response->getBody()->write($data);
                        if ($partialContent) {
                            // output the right headers for partial content
                            return $response->withStatus(206)
                                ->withHeader('Content-Type', \HomeDocs\Utils::getMimeType($attachment->name))
                                ->withHeader('Content-Disposition', (isset($args['inline']) ? 'inline' : 'attachment') . '; filename="' . basename((string) $attachment->name) . '"')
                                ->withHeader('Content-Length', (string) $length)
                                ->withHeader('Content-Range', 'bytes ' . $offset . '-' . ($offset + $length - 1) . '/' . $attachmentSize)
                                ->withHeader('Accept-Ranges', 'bytes');
                        } else {
                            return $response->withStatus(200)
                                ->withHeader('Content-Type', \HomeDocs\Utils::getMimeType($attachment->name))
                                ->withHeader('Content-Disposition', (isset($args['inline']) ? 'inline' : 'attachment') . '; filename="' . basename((string) $attachment->name) . '"')
                                ->withHeader('Content-Length', (string) $attachmentSize)
                                ->withHeader('Accept-Ranges', 'bytes');
                        }
                    } else {
                        throw new \HomeDocs\Exception\NotFoundException($args['id'] ?? "");
                    }
                });

                $routeCollectorProxy->post('[/{id}]', function (Request $request, Response $response, array $args) use ($app, $initialState) {
                    $uploadedFiles = $request->getUploadedFiles();
                    $file = $uploadedFiles['file'] ?? null;
                    if ($file) {
                        if ($file->getError() === UPLOAD_ERR_INI_SIZE) {
                            throw new \HomeDocs\Exception\UploadException("Content too large", 413);
                        } else {
                            $attachment = new \HomeDocs\Attachment(
                                $app->getContainer()->get('settings')['paths']['storage'],
                                $args['id'] ?? \HomeDocs\Utils::uuidv4(),
                                $uploadedFiles["file"]->getClientFilename(),
                                $uploadedFiles["file"]->getSize()
                            );
                            $attachment->add($app->getContainer()->get(\aportela\DatabaseWrapper\DB::class), $uploadedFiles["file"]);
                            $payload = json_encode(
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
                            if (json_last_error() !== JSON_ERROR_NONE) {
                                throw new \HomeDocs\Exception\JSONSerializerException(json_last_error_msg());
                            }

                            $response->getBody()->write($payload);
                            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                        }
                    } else {
                        throw new \HomeDocs\Exception\InvalidParamsException("file");
                    }
                });

                $routeCollectorProxy->delete('/{id}', function (Request $request, Response $response, array $args) use ($app, $initialState) {
                    $attachment = new \HomeDocs\Attachment(
                        $app->getContainer()->get('settings')['paths']['storage'],
                        $args['id']
                    );
                    $dbh = $app->getContainer()->get(\aportela\DatabaseWrapper\DB::class);
                    if ($attachment->isLinkedToDocument($dbh)) {
                        throw new \HomeDocs\Exception\AccessDeniedException();
                    } else {
                        $attachment->remove($dbh);
                        $payload = json_encode(
                            [
                                'initialState' => $initialState,
                            ]
                        );
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            throw new \HomeDocs\Exception\JSONSerializerException(json_last_error_msg());
                        }

                        $response->getBody()->write($payload);
                        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                    }
                });
            })->add(\HomeDocs\Middleware\CheckAuth::class);

            $routeCollectorProxy->get('/tag-cloud', function (Request $request, Response $response, array $args) use ($app, $initialState) {
                $payload = json_encode(
                    [
                        'initialState' => $initialState,
                        'tags' => \HomeDocs\Tag::getCloud($app->getContainer()->get(\aportela\DatabaseWrapper\DB::class))
                    ]
                );
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \HomeDocs\Exception\JSONSerializerException(json_last_error_msg());
                }

                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            })->add(\HomeDocs\Middleware\CheckAuth::class);

            $routeCollectorProxy->get('/tags', function (Request $request, Response $response, array $args) use ($app, $initialState) {
                $payload = json_encode(
                    [
                        'initialState' => $initialState,
                        'tags' => \HomeDocs\Tag::search($app->getContainer()->get(\aportela\DatabaseWrapper\DB::class))
                    ]
                );
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \HomeDocs\Exception\JSONSerializerException(json_last_error_msg());
                }

                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            })->add(\HomeDocs\Middleware\CheckAuth::class);

            $routeCollectorProxy->group('/stats', function (RouteCollectorProxy $routeCollectorProxy) use ($app, $initialState): void {
                $routeCollectorProxy->get('/total-published-documents', function (Request $request, Response $response, array $args) use ($app, $initialState) {
                    $payload = json_encode(
                        [
                            'initialState' => $initialState,
                            'count' => \HomeDocs\Stats::getTotalPublishedDocuments($app->getContainer()->get(\aportela\DatabaseWrapper\DB::class))
                        ]
                    );
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new \HomeDocs\Exception\JSONSerializerException(json_last_error_msg());
                    }

                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });

                $routeCollectorProxy->get('/total-uploaded-attachments', function (Request $request, Response $response, array $args) use ($app, $initialState) {
                    $payload = json_encode(
                        [
                            'initialState' => $initialState,
                            'count' => \HomeDocs\Stats::getTotalUploadedAttachments($app->getContainer()->get(\aportela\DatabaseWrapper\DB::class))
                        ]
                    );
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new \HomeDocs\Exception\JSONSerializerException(json_last_error_msg());
                    }

                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });

                $routeCollectorProxy->get('/total-uploaded-attachments-disk-usage', function (Request $request, Response $response, array $args) use ($app, $initialState) {
                    $payload = json_encode(
                        [
                            'initialState' => $initialState,
                            'size' => \HomeDocs\Stats::getTotalUploadedAttachmentsDiskUsage($app->getContainer()->get(\aportela\DatabaseWrapper\DB::class))
                        ]
                    );
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new \HomeDocs\Exception\JSONSerializerException(json_last_error_msg());
                    }

                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });

                $routeCollectorProxy->get('/heatmap-activity-data', function (Request $request, Response $response, array $args) use ($app, $initialState) {
                    $queryParams = $request->getQueryParams();
                    $payload = json_encode(
                        [
                            'initialState' => $initialState,
                            'heatmap' => \HomeDocs\Stats::getActivityHeatMapData(
                                $app->getContainer()->get(\aportela\DatabaseWrapper\DB::class),
                                $queryParams["fromTimestamp"] ?? 0
                            )
                        ]
                    );
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new \HomeDocs\Exception\JSONSerializerException(json_last_error_msg());
                    }

                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });
            })->add(\HomeDocs\Middleware\CheckAuth::class);
        }
    )->add(\HomeDocs\Middleware\JWT::class)->add(\HomeDocs\Middleware\APIExceptionCatcher::class);
};
