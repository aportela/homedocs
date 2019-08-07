<?php

    declare(strict_types=1);

    namespace Forms;

    use Slim\Http\Request;
    use Slim\Http\Response;

    $this->app->get('/', function (Request $request, Response $response, array $args) {
        $this->logger->info($request->getOriginalMethod() . " " . $request->getUri()->getPath());
        return $this->view->render($response, 'index.html.twig', array(
            'settings' => $this->settings["twigParams"],
            'initialState' => json_encode(
                array(
                    'allowSignUp' => $this->get('settings')['common']['allowSignUp'],
                    'defaultResultsPage' => $this->get('settings')['common']['defaultResultsPage'],
                    'productionEnvironment' => $this->get('settings')['twigParams']['production']
                )
            )
        ));
    });
?>