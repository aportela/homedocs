<?php

use Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {
    $app->get('/', function (Request $request, Response $response, array $args) {
        //$logger = $group->get(\Spieldose\Logger\HTTPRequestLogger::class);
        //$logger->info($request->getMethod() . " " . $request->getUri()->getPath());
        $settings = $this->get('settings');
        return $this->get('Twig')->render($response, 'index.html.twig', [
            'initialState' => json_encode(\HomeDocs\Utils::getInitialState($this))
        ]);
    })->add(\HomeDocs\Middleware\JWT::class);

    $app->group(
        '/api2',
        function (RouteCollectorProxy $group) {

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
                                'initialState' => json_encode(\HomeDocs\Utils::getInitialState($this))
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
                $settings = $this->get('settings');
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
                        'initialState' => json_encode(\HomeDocs\Utils::getInitialState($this))
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
                        'initialState' => json_encode(\HomeDocs\Utils::getInitialState($this))
                    ]
                );
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            });

            $group->post('/document/search-recent', function (Request $request, Response $response, array $args) {
                $settings = $this->get('settings');
                $params = $request->getParsedBody();
                $payload = json_encode(
                    [
                        'initialState' => json_encode(\HomeDocs\Utils::getInitialState($this)),
                        'recentDocuments' => \HomeDocs\Document::searchRecent(
                            $this->get(\aportela\DatabaseWrapper\DB::class),
                            $params["count"] ?? $settings["common"]["defaultResultsPage"]
                        )
                    ]
                );
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            });

            $group->post('/document/search', function (Request $request, Response $response, array $args) {
                $settings = $this->get('settings');
                $params = $request->getParsedBody();
                $payload = json_encode(
                    [
                        'initialState' => json_encode(\HomeDocs\Utils::getInitialState($this)),
                        'data' => \HomeDocs\Document::search(
                            $this->get(\aportela\DatabaseWrapper\DB::class),
                            intval($params["currentPage"] ?? 1),
                            intval($params["resultsPage"] ?? $settings["common"]["defaultResultsPage"]),
                            [
                                "title" => $params["title"] ?? null,
                                "description" => $params["description"] ?? null,
                                "fromTimestampCondition" => intval($params["fromTimestampCondition"] ?? 0),
                                "toTimestampCondition" => intval($params["toTimestampCondition"] ?? 0),
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

            $group->get('/document/{id}', function (Request $request, Response $response, array $args) {
                $document = new \HomeDocs\Document();
                $document->id = $args['id'];
                $document->get($this->get(\aportela\DatabaseWrapper\DB::class));
                $payload = json_encode(
                    [
                        'initialState' => json_encode(\HomeDocs\Utils::getInitialState($this)),
                        'data' => $document
                    ]
                );
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            });

            $group->post('/document/{id}', function (Request $request, Response $response, array $args) {
                $params = $request->getParsedBody();
                $documentFiles = $params["files"] ?? [];
                $files = array();
                if (is_array($documentFiles) && count($documentFiles) > 0) {
                    foreach ($documentFiles as $documentFile) {
                        $files[] = new \HomeDocs\File(
                            $documentFile["id"],
                            $documentFile["name"],
                            $documentFile["size"],
                            $documentFile["hash"]
                        );
                    }
                }
                $document = new \HomeDocs\Document(
                    $args['id'],
                    $params["title"] ?? "",
                    $params["description"] ?? "",
                    $params["tags"] ?? [],
                    $files
                );
                $document->add($this->get(\aportela\DatabaseWrapper\DB::class));
                $payload = json_encode(
                    [
                        'initialState' => json_encode(\HomeDocs\Utils::getInitialState($this))
                    ]
                );
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            });

            $group->put('/document/{id}', function (Request $request, Response $response, array $args) {
                $params = $request->getParsedBody();
                $documentFiles = $params["files"] ?? [];
                $files = array();
                if (is_array($documentFiles) && count($documentFiles) > 0) {
                    foreach ($documentFiles as $documentFile) {
                        $files[] = new \HomeDocs\File(
                            $documentFile["id"],
                            $documentFile["name"],
                            $documentFile["size"],
                            $documentFile["hash"]
                        );
                    }
                }
                $dbh = $this->get(\aportela\DatabaseWrapper\DB::class);
                $document = new \HomeDocs\Document(
                    $args['id']
                );
                // test existence && check permissions
                $document->get($dbh);
                $document = new \HomeDocs\Document(
                    $args['id'],
                    $params["title"] ?? "",
                    $params["description"] ?? "",
                    $params["tags"] ?? [],
                    $files
                );
                $document->update($dbh);
                $payload = json_encode(
                    [
                        'initialState' => json_encode(\HomeDocs\Utils::getInitialState($this))
                    ]
                );
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            });

            $group->delete('/document/{id}', function (Request $request, Response $response, array $args) {
                $document = new \HomeDocs\Document(
                    $args['id']
                );
                $dbh = $this->get(\aportela\DatabaseWrapper\DB::class);
                // test existence && check permissions
                $document->get($dbh);
                $document->delete($dbh);
                $payload = json_encode(
                    [
                        'initialState' => json_encode(\HomeDocs\Utils::getInitialState($this))
                    ]
                );
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            });

            $group->get('/file/{id}', function (Request $request, Response $response, array $args) {
                $route = $request->getAttribute('route');
                $file = new \HomeDocs\File(
                    $args['id']
                );
                $file->get($this->get(\aportela\DatabaseWrapper\DB::class));
                if (file_exists($file->localStoragePath)) {
                    $partialContent = false;
                    $filesize = filesize($file->localStoragePath);
                    $offset = 0;
                    $length = $filesize;
                    if (isset($_SERVER['HTTP_RANGE'])) {
                        // if the HTTP_RANGE header is set we're dealing with partial content
                        $partialContent = true;
                        // find the requested range
                        // this might be too simplistic, apparently the client can request
                        // multiple ranges, which can become pretty complex, so ignore it for now
                        preg_match('/bytes=(\d+)-(\d+)?/', $_SERVER['HTTP_RANGE'], $matches);
                        $offset = intval($matches[1]);
                        $length = ((isset($matches[2])) ? intval($matches[2]) : $file->size) - $offset;
                    }
                    $f = fopen($file->localStoragePath, 'r');
                    fseek($f, $offset);
                    $data = fread($f, $length);
                    fclose($f);
                    $response->getBody()->write($data);
                    if ($partialContent) {
                        // output the right headers for partial content
                        return $response->withStatus(206)
                            ->withHeader('Content-Type', \HomeDocs\Utils::getMimeType($file->name))
                            ->withHeader('Content-Disposition', 'attachment; filename="' . basename($file->name) . '"')
                            ->withHeader('Content-Length', $filesize)
                            ->withHeader('Content-Range', 'bytes ' . $offset . '-' . ($offset + $length - 1) . '/' . $filesize)
                            ->withHeader('Accept-Ranges', 'bytes');
                    } else {
                        return $response->withStatus(200)
                            ->withHeader('Content-Type', \HomeDocs\Utils::getMimeType($file->name))
                            ->withHeader('Content-Disposition', 'attachment; filename="' . basename($file->name) . '"')
                            ->withHeader('Content-Length', $filesize)
                            ->withHeader('Accept-Ranges', 'bytes');
                    }
                } else {
                    throw new \HomeDocs\Exception\NotFoundException($args['id'] ?? "");
                }
            });

            $group->post('/file/{id}', function (Request $request, Response $response, array $args) {
                $files = $request->getUploadedFiles();
                $file = new \HomeDocs\File(
                    $args['id'],
                    $files["file"]->getClientFilename(),
                    $files["file"]->getSize()
                );
                $file->add($this->get(\aportela\DatabaseWrapper\DB::class), $files["file"]);
                $payload = json_encode(
                    [
                        'initialState' => json_encode(\HomeDocs\Utils::getInitialState($this)),
                        'data' => array(
                            "id" => $file->id,
                            "name" => $file->name,
                            "size" => $file->size,
                            "hash" => $file->hash,
                            "uploadedOnTimestamp" => time()
                        )
                    ]
                );
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            });

            $group->get('/tag-cloud', function (Request $request, Response $response, array $args) {
                $payload = json_encode(
                    [
                        'initialState' => json_encode(\HomeDocs\Utils::getInitialState($this)),
                        'tags' => \HomeDocs\Tag::getCloud($this->get(\aportela\DatabaseWrapper\DB::class))
                    ]
                );
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            });

            $group->post('/tag/search', function (Request $request, Response $response, array $args) {
                $payload = json_encode(
                    [
                        'initialState' => json_encode(\HomeDocs\Utils::getInitialState($this)),
                        'data' => \HomeDocs\Tag::search($this->get(\aportela\DatabaseWrapper\DB::class))
                    ]
                );
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            });
        }
    )->add(\HomeDocs\Middleware\JWT::class)->add(\HomeDocs\Middleware\APIExceptionCatcher::class);
};
