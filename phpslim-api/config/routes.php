<?php

use Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

// TODO: CheckAuth & JWT middlewares (combine ?)
return function (App $app) {
    $app->get('/', function (Request $request, Response $response, array $args) {
        return $this->get('Twig')->render($response, 'index-quasar.html.twig', []);
    })->add(\HomeDocs\Middleware\JWT::class);

    $app->group(
        '/api2',
        function (RouteCollectorProxy $group) {
            $group->get('/initial_state', function (Request $request, Response $response, array $args) {
                $payload = json_encode(
                    [
                        'initialState' => \HomeDocs\Utils::getInitialState($this)
                    ]
                );
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            });

            $group->post('/user/sign-up', function (Request $request, Response $response, array $args) {
                $settings = $this->get('settings');
                if ($settings['common']['allowSignUp']) {
                    $params = $request->getParsedBody();
                    $db = $this->get(\aportela\DatabaseWrapper\DB::class);
                    if (\HomeDocs\User::isEmailUsed($db, $params["email"] ?? "")) {
                        throw new \HomeDocs\Exception\AlreadyExistsException("email");
                    } else {
                        $user = new \HomeDocs\User(
                            $params["id"] ?? "",
                            $params["email"] ?? "",
                            $params["password"] ?? ""
                        );
                        $user->add($db);
                        $payload = json_encode(
                            [
                                'initialState' => \HomeDocs\Utils::getInitialState($this)
                            ]
                        );
                        $response->getBody()->write($payload);
                        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                    }
                } else {
                    throw new \HomeDocs\Exception\AccessDeniedException("");
                }
            });

            $group->post('/user/sign-in', function (Request $request, Response $response, array $args) {
                $params = $request->getParsedBody();
                $db = $this->get(\aportela\DatabaseWrapper\DB::class);
                $user = new \HomeDocs\User(
                    "",
                    $params["email"] ?? "",
                    $params["password"] ?? ""
                );
                $user->signIn($db);
                $payload = json_encode(
                    [
                        'initialState' => \HomeDocs\Utils::getInitialState($this)
                    ]
                );
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            });

            $group->post('/user/sign-out', function (Request $request, Response $response, array $args) {
                $settings = $this->get('settings');
                \HomeDocs\User::signOut();
                $payload = json_encode(
                    [
                        'initialState' => \HomeDocs\Utils::getInitialState($this)
                    ]
                );
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            });

            $group->group('/user', function (RouteCollectorProxy $group) {
                $group->get('/profile', function (Request $request, Response $response, array $args) {
                    $user = new \HomeDocs\User();
                    $user->id = \HomeDocs\UserSession::getUserId();
                    $user->get($this->get(\aportela\DatabaseWrapper\DB::class));
                    unset($user->password);
                    unset($user->passwordHash);
                    $payload = json_encode(
                        [
                            'initialState' => \HomeDocs\Utils::getInitialState($this),
                            'data' => $user
                        ]
                    );
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });

                $group->put('/profile', function (Request $request, Response $response, array $args) {
                    $params = $request->getParsedBody();
                    $db = $this->get(\aportela\DatabaseWrapper\DB::class);
                    $user = new \HomeDocs\User(
                        \HomeDocs\UserSession::getUserId(),
                        "",
                        ""
                    );
                    $user->get($db);
                    if ($params["email"] != \HomeDocs\UserSession::getEmail()) {
                        $tmpUser = new \HomeDocs\User(
                            "",
                            $params["email"]
                        );
                        if ($tmpUser->exists($db)) {
                            throw new \HomeDocs\Exception\AlreadyExistsException("email");
                        }
                    }
                    $user->email = $params["email"] ?? "";
                    $user->password = $params["password"] ?? "";
                    $user->update($db);
                    unset($user->password);
                    unset($user->passwordHash);
                    $payload = json_encode(
                        [
                            'initialState' => \HomeDocs\Utils::getInitialState($this),
                            'data' => $user
                        ]
                    );
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });
            })->add(\HomeDocs\Middleware\CheckAuth::class);

            $group->group('/search', function (RouteCollectorProxy $group) {
                // TODO: is this required ? can be recplaced only with /search/document with custom params
                $group->post('/recent_documents', function (Request $request, Response $response, array $args) {
                    $settings = $this->get('settings');
                    $params = $request->getParsedBody();
                    $payload = json_encode(
                        [
                            'initialState' => \HomeDocs\Utils::getInitialState($this),
                            'recentDocuments' => \HomeDocs\Document::searchRecent(
                                $this->get(\aportela\DatabaseWrapper\DB::class),
                                $params["count"] ?? $settings["common"]["defaultResultsPage"]
                            )
                        ]
                    );
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });

                $group->post('/document', function (Request $request, Response $response, array $args) {
                    $settings = $this->get('settings');
                    $params = $request->getParsedBody();
                    $payload = json_encode(
                        [
                            'initialState' => \HomeDocs\Utils::getInitialState($this),
                            'results' => \HomeDocs\Document::search(
                                $this->get(\aportela\DatabaseWrapper\DB::class),
                                intval($params["currentPage"] ?? 1),
                                intval($params["resultsPage"] ?? $settings["common"]["defaultResultsPage"]),
                                [
                                    "title" => $params["title"] ?? null,
                                    "description" => $params["description"] ?? null,
                                    "notesBody" => $params["notesBody"] ?? null,
                                    "fromCreationTimestampCondition" => intval($params["fromCreationTimestampCondition"] ?? 0),
                                    "toCreationTimestampCondition" => intval($params["toCreationTimestampCondition"] ?? 0),
                                    "fromLastUpdateTimestampCondition" => intval($params["fromLastUpdateTimestampCondition"] ?? 0),
                                    "toLastUpdateTimestampCondition" => intval($params["toLastUpdateTimestampCondition"] ?? 0),
                                    "fromUpdatedOnTimestampCondition" => intval($params["fromUpdatedOnTimestampCondition"] ?? 0),
                                    "toUpdatedOnTimestampCondition" => intval($params["toUpdatedOnTimestampCondition"] ?? 0),
                                    "tags" => $params["tags"] ?? [],
                                ],
                                $params["sortBy"] ?? null,
                                $params["sortOrder"] ?? null
                            )
                        ]
                    );
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });
            })->add(\HomeDocs\Middleware\CheckAuth::class);

            $group->group('/document', function (RouteCollectorProxy $group) {
                $group->get('/{id}', function (Request $request, Response $response, array $args) {
                    $document = new \HomeDocs\Document();
                    $document->id = $args['id'];
                    $document->setRootStoragePath($this->get('settings')['paths']['storage']);
                    $document->get($this->get(\aportela\DatabaseWrapper\DB::class));
                    $payload = json_encode(
                        [
                            'initialState' => \HomeDocs\Utils::getInitialState($this),
                            'document' => $document
                        ]
                    );
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });

                $group->get('/{id}/notes', function (Request $request, Response $response, array $args) {
                    $document = new \HomeDocs\Document();
                    $document->id = $args['id'];
                    $document->setRootStoragePath($this->get('settings')['paths']['storage']);
                    $document->get($this->get(\aportela\DatabaseWrapper\DB::class));
                    $payload = json_encode(
                        [
                            'initialState' => \HomeDocs\Utils::getInitialState($this),
                            'notes' => $document->notes
                        ]
                    );
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });

                $group->get('/{id}/attachments', function (Request $request, Response $response, array $args) {
                    $document = new \HomeDocs\Document();
                    $document->id = $args['id'];
                    $document->setRootStoragePath($this->get('settings')['paths']['storage']);
                    $document->get($this->get(\aportela\DatabaseWrapper\DB::class));
                    $payload = json_encode(
                        [
                            'initialState' => \HomeDocs\Utils::getInitialState($this),
                            'attachments' => $document->attachments
                        ]
                    );
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });

                $group->post('/{id}', function (Request $request, Response $response, array $args) {
                    $params = $request->getParsedBody();
                    $documentAttachments = $params["attachments"] ?? [];
                    $rootStoragePath = $this->get('settings')['paths']['storage'];
                    $attachments = array();
                    if (is_array($documentAttachments) && count($documentAttachments) > 0) {
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
                    $notes = array();
                    if (is_array($documentNotes) && count($documentNotes) > 0) {
                        foreach ($documentNotes as $documentNote) {
                            $notes[] = new \HomeDocs\Note(
                                $documentNote["id"],
                                $documentNote["createdOnTimestamp"],
                                $documentNote["body"]
                            );
                        }
                    }
                    $rootStoragePath = $this->get('settings')['paths']['storage'];
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
                    $dbh = $this->get(\aportela\DatabaseWrapper\DB::class);
                    $dbh->beginTransaction();
                    try {
                        $document->add($dbh);
                        $dbh->commit();
                    } catch (\aportela\DatabaseWrapper\Exception\DBException $e) {
                        $dbh->rollBack();
                        throw $e;
                    }
                    $document->setRootStoragePath($rootStoragePath);
                    $document->get($dbh);
                    $payload = json_encode(
                        [
                            'initialState' => \HomeDocs\Utils::getInitialState($this),
                            'document' => $document
                        ]
                    );
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });

                $group->put('/{id}', function (Request $request, Response $response, array $args) {
                    $params = $request->getParsedBody();
                    $dbh = $this->get(\aportela\DatabaseWrapper\DB::class);
                    $document = new \HomeDocs\Document(
                        $args['id']
                    );
                    // test existence && check permissions
                    $rootStoragePath = $this->get('settings')['paths']['storage'];
                    $document->setRootStoragePath($rootStoragePath);
                    $document->get($dbh);
                    $documentAttachments = $params["attachments"] ?? [];
                    $attachments = array();
                    if (is_array($documentAttachments) && count($documentAttachments) > 0) {
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
                    $notes = array();
                    if (is_array($documentNotes) && count($documentNotes) > 0) {
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
                    } catch (\aportela\DatabaseWrapper\Exception\DBException $e) {
                        $dbh->rollBack();
                        throw $e;
                    }
                    $document->get($this->get(\aportela\DatabaseWrapper\DB::class));
                    $payload = json_encode(
                        [
                            'initialState' => \HomeDocs\Utils::getInitialState($this),
                            'document' => $document
                        ]
                    );
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });

                $group->delete('/{id}', function (Request $request, Response $response, array $args) {
                    $document = new \HomeDocs\Document(
                        $args['id']
                    );
                    $document->setRootStoragePath($this->get('settings')['paths']['storage']);
                    $dbh = $this->get(\aportela\DatabaseWrapper\DB::class);
                    // test existence && check permissions
                    $document->get($dbh);
                    try {
                        $dbh->beginTransaction();
                        $document->delete($dbh);
                        $dbh->commit();
                    } catch (\aportela\DatabaseWrapper\Exception\DBException $e) {
                        $dbh->rollBack();
                        throw $e;
                    }
                    $payload = json_encode(
                        [
                            'initialState' => \HomeDocs\Utils::getInitialState($this)
                        ]
                    );
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });
            })->add(\HomeDocs\Middleware\CheckAuth::class);

            $group->group('/attachment', function (RouteCollectorProxy $group) {
                $group->get('/{id}', function (Request $request, Response $response, array $args) {
                    $route = $request->getAttribute('route');
                    $attachment = new \HomeDocs\Attachment(
                        $this->get('settings')['paths']['storage'],
                        $args['id']
                    );
                    $attachment->get($this->get(\aportela\DatabaseWrapper\DB::class));
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
                            preg_match('/bytes=(\d+)-(\d+)?/', $_SERVER['HTTP_RANGE'], $matches);
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
                                ->withHeader('Content-Disposition', 'attachment; filename="' . basename($attachment->name) . '"')
                                ->withHeader('Content-Length', (string) $length)
                                ->withHeader('Content-Range', 'bytes ' . $offset . '-' . ($offset + $length - 1) . '/' . $attachmentSize)
                                ->withHeader('Accept-Ranges', 'bytes');
                        } else {
                            return $response->withStatus(200)
                                ->withHeader('Content-Type', \HomeDocs\Utils::getMimeType($attachment->name))
                                ->withHeader('Content-Disposition', 'attachment; filename="' . basename($attachment->name) . '"')
                                ->withHeader('Content-Length', (string) $attachmentSize)
                                ->withHeader('Accept-Ranges', 'bytes');
                        }
                    } else {
                        throw new \HomeDocs\Exception\NotFoundException($args['id'] ?? "");
                    }
                })->add(\HomeDocs\Middleware\CheckAuth::class);

                $group->post('[/{id}]', function (Request $request, Response $response, array $args) {
                    $uploadedFiles = $request->getUploadedFiles();
                    $attachment = new \HomeDocs\Attachment(
                        $this->get('settings')['paths']['storage'],
                        isset($args['id']) ? $args['id'] : \HomeDocs\Utils::uuidv4(),
                        $uploadedFiles["file"]->getClientFilename(),
                        $uploadedFiles["file"]->getSize()
                    );
                    $attachment->add($this->get(\aportela\DatabaseWrapper\DB::class), $uploadedFiles["file"]);
                    $payload = json_encode(
                        [
                            'initialState' => \HomeDocs\Utils::getInitialState($this),
                            'data' => array(
                                "id" => $attachment->id,
                                "name" => $attachment->name,
                                "size" => $attachment->size,
                                "hash" => $attachment->hash,
                                "createdOnTimestamp" => $attachment->createdOnTimestamp
                            )
                        ]
                    );
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                })->add(\HomeDocs\Middleware\CheckAuth::class);

                $group->delete('/{id}', function (Request $request, Response $response, array $args) {
                    $attachment = new \HomeDocs\Attachment(
                        $this->get('settings')['paths']['storage'],
                        $args['id']
                    );
                    $dbh = $this->get(\aportela\DatabaseWrapper\DB::class);
                    if ($attachment->isLinkedToDocument($dbh)) {
                        throw new \HomeDocs\Exception\AccessDeniedException();
                    } else {
                        $attachment->remove($dbh);
                        $payload = json_encode(
                            [
                                'initialState' => \HomeDocs\Utils::getInitialState($this),
                            ]
                        );
                        $response->getBody()->write($payload);
                        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                    }
                })->add(\HomeDocs\Middleware\CheckAuth::class);
            });

            $group->get('/tag-cloud', function (Request $request, Response $response, array $args) {
                $payload = json_encode(
                    [
                        'initialState' => \HomeDocs\Utils::getInitialState($this),
                        'tags' => \HomeDocs\Tag::getCloud($this->get(\aportela\DatabaseWrapper\DB::class))
                    ]
                );
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            })->add(\HomeDocs\Middleware\CheckAuth::class);

            $group->get('/tags', function (Request $request, Response $response, array $args) {
                $payload = json_encode(
                    [
                        'initialState' => \HomeDocs\Utils::getInitialState($this),
                        'tags' => \HomeDocs\Tag::search($this->get(\aportela\DatabaseWrapper\DB::class))
                    ]
                );
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            })->add(\HomeDocs\Middleware\CheckAuth::class);

            $group->group('/stats', function (RouteCollectorProxy $group) {
                $group->get('/total-published-documents', function (Request $request, Response $response, array $args) {
                    $payload = json_encode(
                        [
                            'initialState' => \HomeDocs\Utils::getInitialState($this),
                            'count' => \HomeDocs\Stats::getTotalPublishedDocuments($this->get(\aportela\DatabaseWrapper\DB::class))
                        ]
                    );
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });

                $group->get('/total-uploaded-attachments', function (Request $request, Response $response, array $args) {
                    $payload = json_encode(
                        [
                            'initialState' => \HomeDocs\Utils::getInitialState($this),
                            'count' => \HomeDocs\Stats::getTotalUploadedAttachments($this->get(\aportela\DatabaseWrapper\DB::class))
                        ]
                    );
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });

                $group->get('/total-uploaded-attachments-disk-usage', function (Request $request, Response $response, array $args) {
                    $payload = json_encode(
                        [
                            'initialState' => \HomeDocs\Utils::getInitialState($this),
                            'size' => \HomeDocs\Stats::getTotalUploadedAttachmentsDiskUsage($this->get(\aportela\DatabaseWrapper\DB::class))
                        ]
                    );
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });

                $group->get('/heatmap-activity-data', function (Request $request, Response $response, array $args) {
                    $queryParams = $request->getQueryParams();
                    $payload = json_encode(
                        [
                            'initialState' => \HomeDocs\Utils::getInitialState($this),
                            'heatmap' => \HomeDocs\Stats::getActivityHeatMapData(
                                $this->get(\aportela\DatabaseWrapper\DB::class),
                                isset($queryParams["fromTimestamp"]) ? $queryParams["fromTimestamp"] : 0
                            )
                        ]
                    );
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });
            })->add(\HomeDocs\Middleware\CheckAuth::class);
        }
    )->add(\HomeDocs\Middleware\JWT::class)->add(\HomeDocs\Middleware\APIExceptionCatcher::class);
};
