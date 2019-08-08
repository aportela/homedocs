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

    })->add(new \HomeDocs\Middleware\APIExceptionCatcher($this->app->getContainer()));
?>