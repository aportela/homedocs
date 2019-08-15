<?php

    declare(strict_types=1);

    namespace HomeDocs;

    use Slim\Http\Request;
    use Slim\Http\Response;

    $this->app->get('/', function (Request $request, Response $response, array $args) {
        $this->logger->info($request->getOriginalMethod() . " " . $request->getUri()->getPath());
        return $this->view->render($response, 'index.html.twig', array(
            'settings' => $this->settings["twigParams"],
            'initialState' => json_encode(\HomeDocs\Utils::getInitialState($this))
        ));
    });

    $this->app->group("/api2", function() {

        $this->post('/user/sign-up', function (Request $request, Response $response, array $args) {
            if ($this->get('settings')['common']['allowSignUp']) {
                $db = new \HomeDocs\Database\DB($this);
                if (\HomeDocs\User::isEmailUsed($db, $request->getParam("email", ""))) {
                    throw new \HomeDocs\Exception\AlreadyExistsException("email");
                } else {
                    $user = new \HomeDocs\User(
                        $request->getParam("id", ""),
                        $request->getParam("email", ""),
                        $request->getParam("password", "")
                    );
                    $user->add($db);
                    return $response->withJson(
                        [
                            'initialState' => \HomeDocs\Utils::getInitialState($this)
                        ],
                        200
                    );
                }
            } else {
                throw new \HomeDocs\Exception\AccessDeniedException("");
            }
        });

        $this->post('/user/sign-in', function (Request $request, Response $response, array $args) {
            $user = new \HomeDocs\User(
                "",
                $request->getParam("email", ""),
                $request->getParam("password", "")
            );
            $user->signIn(new \HomeDocs\Database\DB($this));
            return $response->withJson(
                [
                    'initialState' => \HomeDocs\Utils::getInitialState($this)
                ],
                200
            );
        });

        $this->post('/user/sign-out', function (Request $request, Response $response, array $args) {
            \HomeDocs\User::signOut();
            return $response->withJson(
                [
                    'initialState' => \HomeDocs\Utils::getInitialState($this)
                ],
                200
            );
        });

        $this->post('/document/search-recent', function (Request $request, Response $response, array $args) {
            $data = \HomeDocs\Document::searchRecent(
                new \HomeDocs\Database\DB($this),
                $request->getParam("count", 16)
            );
            return $response->withJson(
                [
                    'initialState' => \HomeDocs\Utils::getInitialState($this),
                    'data' => $data
                ],
                200
            );
        });

        $this->post('/document/search', function (Request $request, Response $response, array $args) {
            $filter = array(
                "title" => $request->getParam("title", null),
                "description" => $request->getParam("description", null),
                "fromTimestampCondition" => intval($request->getParam("fromTimestampCondition", 0)),
                "toTimestampCondition" => intval($request->getParam("toTimestampCondition", 0)),
                "tags" => $request->getParam("tags", array()),
            );
            $data = \HomeDocs\Document::search(
                new \HomeDocs\Database\DB($this),
                intval($request->getParam("currentPage", 1)),
                intval($request->getParam("resultsPage", $this->get('settings')['common']['defaultResultsPage'])),
                $filter,
                $request->getParam("sortBy", ""),
                $request->getParam("sortOrder", "")
            );
            return $response->withJson(
                [
                    'initialState' => \HomeDocs\Utils::getInitialState($this),
                    'data' => $data
                ],
                200
            );
        });

        $this->get('/document/{id}', function (Request $request, Response $response, array $args) {
            $route = $request->getAttribute('route');
            $document = new \HomeDocs\Document();
            $document->id = $route->getArgument("id");
            $document->get(new \HomeDocs\Database\DB($this));
            return $response->withJson(
                [
                    'initialState' => \HomeDocs\Utils::getInitialState($this),
                    'data' => $document
                ],
                200
            );
        });

        $this->post('/document/{id}', function (Request $request, Response $response, array $args) {
            $route = $request->getAttribute('route');
            $documentFiles = $request->getParam("files", array());
            $files = array();
            if (is_array($documentFiles) && count($documentFiles) > 0) {
                foreach($documentFiles as $documentFile) {
                    $files[] = new \HomeDocs\File(
                        $documentFile["id"],
                        $documentFile["name"],
                        $documentFile["size"],
                        $documentFile["hash"]
                    );
                }
            }
            $document = new \HomeDocs\Document(
                $route->getArgument("id"),
                $request->getParam("title", ""),
                $request->getParam("description", ""),
                $request->getParam("tags", array()),
                $files
            );
            $document->add(new \HomeDocs\Database\DB($this));
            return $response->withJson(
                [
                    'initialState' => \HomeDocs\Utils::getInitialState($this)
                ],
                200
            );
        });

        $this->put('/document/{id}', function (Request $request, Response $response, array $args) {
            $route = $request->getAttribute('route');
            $documentFiles = $request->getParam("files", array());
            $files = array();
            if (is_array($documentFiles) && count($documentFiles) > 0) {
                foreach($documentFiles as $documentFile) {
                    $files[] = new \HomeDocs\File(
                        $documentFile["id"],
                        $documentFile["name"],
                        $documentFile["size"],
                        $documentFile["hash"]
                    );
                }
            }
            $dbh = new \HomeDocs\Database\DB($this);
            $document = new \HomeDocs\Document(
                $route->getArgument("id")
            );
            // test existence && check permissions
            $document->get($dbh);
            $document = new \HomeDocs\Document(
                $route->getArgument("id"),
                $request->getParam("title", ""),
                $request->getParam("description", ""),
                $request->getParam("tags", array()),
                $files
            );
            $document->update($dbh);
            return $response->withJson(
                [
                    'initialState' => \HomeDocs\Utils::getInitialState($this)
                ],
                200
            );
        });

        $this->delete('/document/{id}', function (Request $request, Response $response, array $args) {
            $route = $request->getAttribute('route');
            $document = new \HomeDocs\Document(
                $route->getArgument("id")
            );
            $dbh = new \HomeDocs\Database\DB($this);
            // test existence && check permissions
            $document->get($dbh);
            $document->delete($dbh);
            return $response->withJson(
                [
                    'initialState' => \HomeDocs\Utils::getInitialState($this)
                ],
                200
            );
        });

        $this->get('/file/{id}', function (Request $request, Response $response, array $args) {
            $route = $request->getAttribute('route');
            $file = new \HomeDocs\File(
                $route->getArgument("id")
            );
            $file->get(new \HomeDocs\Database\DB($this));
            if (file_exists($file->localStoragePath)) {
                $filesize = filesize($file->localStoragePath);
                $offset = 0;
                $length = $filesize;
                // https://stackoverflow.com/a/157447
                if (isset($_SERVER['HTTP_RANGE'])) {
                    // if the HTTP_RANGE header is set we're dealing with partial content
                    $partialContent = true;
                    // find the requested range
                    // this might be too simplistic, apparently the client can request
                    // multiple ranges, which can become pretty complex, so ignore it for now
                    preg_match('/bytes=(\d+)-(\d+)?/', $_SERVER['HTTP_RANGE'], $matches);
                    $offset = intval($matches[1]);
                    $length = ((isset($matches[2])) ? intval($matches[2]) : $filesize) - $offset;
                } else {
                    $partialContent = false;
                }
                $f = fopen($file->localStoragePath, 'r');
                fseek($f, $offset);
                $data = fread($f, $length);
                fclose($f);
                if ($partialContent) {
                    // output the right headers for partial content
                    return $response->withStatus(206)
                    ->withHeader('Content-Type', \HomeDocs\Utils::getMimeType($file->name))
                    ->withHeader('Content-Disposition', 'attachment; filename="' . basename($file->name) . '"')
                    ->withHeader('Content-Length', $filesize)
                    ->withHeader('Content-Range', 'bytes ' . $offset . '-' . ($offset + $length - 1) . '/' . $filesize)
                    ->withHeader('Accept-Ranges', 'bytes')
                    ->write($data);
                } else {
                    return $response->withStatus(200)
                        ->withHeader('Content-Type', \HomeDocs\Utils::getMimeType($file->name))
                        ->withHeader('Content-Disposition', 'attachment; filename="' . basename($file->name) . '"')
                        ->withHeader('Content-Length', $filesize)
                        ->withHeader('Accept-Ranges', 'bytes')
                        ->write($data);
                }
            } else {
                throw new \HomeDocs\Exception\NotFoundException($id);
            }
        });

        $this->post('/file/{id}', function (Request $request, Response $response, array $args) {
            $route = $request->getAttribute('route');
            $files = $request->getUploadedFiles();
            $file = new \HomeDocs\File(
                $route->getArgument("id"),
                $files["file"]->getClientFilename(),
                $files["file"]->getSize()
            );
            $file->add(new \HomeDocs\Database\DB($this), $files["file"]);
            return $response->withJson(
                [
                    'initialState' => \HomeDocs\Utils::getInitialState($this),
                    'data' => array(
                        "id" => $file->id,
                        "name" => $file->name,
                        "size" => $file->size,
                        "hash" => $file->hash,
                        "uploadedOnTimestamp" => time()
                    )
                ],
                200
            );
        });

        $this->get('/tag-cloud', function (Request $request, Response $response, array $args) {
            $data = \HomeDocs\Tag::getCloud(
                new \HomeDocs\Database\DB($this)
            );
            return $response->withJson(
                [
                    'initialState' => \HomeDocs\Utils::getInitialState($this),
                    'data' => $data
                ],
                200
            );
        });

        $this->post('/tag/search', function (Request $request, Response $response, array $args) {
            $data = \HomeDocs\Tag::search(
                new \HomeDocs\Database\DB($this)
            );
            return $response->withJson(
                [
                    'initialState' => \HomeDocs\Utils::getInitialState($this),
                    'data' => $data
                ],
                200
            );
        });

    })->add(new \HomeDocs\Middleware\APIExceptionCatcher($this->app->getContainer()));
?>