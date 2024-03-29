<?php

declare(strict_types=1);

namespace HomeDocs\Middleware;

class APIExceptionCatcher
{
    protected \Psr\Log\LoggerInterface $logger;

    public function __construct(\HomeDocs\Logger\HTTPRequestLogger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * APIExceptionCatcher middleware invokable class
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param  \Psr\Http\Server\RequestHandlerInterface $handler  PSR7 request handler object
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Server\RequestHandlerInterface $handler): \Psr\Http\Message\ResponseInterface
    {
        try {
            $this->logger->debug($request->getMethod() . " " . $request->getUri()->getPath());
            $this->logger->debug($request->getBody());
            $response = $handler->handle($request);
            return $response;
        } catch (\HomeDocs\Exception\InvalidParamsException $e) {
            $this->logger->debug(sprintf("Exception caught (%s) - Message: %s", get_class($e), $e->getMessage()));
            $response = new \Slim\Psr7\Response();
            $payload = json_encode(['invalidOrMissingParams' => explode(",", $e->getMessage())]);
            $response->getBody()->write($payload);
            return ($response)->withStatus(400);
        } catch (\HomeDocs\Exception\AlreadyExistsException $e) {
            $this->logger->debug(sprintf("Exception caught (%s) - Message: %s", get_class($e), $e->getMessage()));
            $response = new \Slim\Psr7\Response();
            $payload = json_encode(['invalidOrMissingParams' => explode(",", $e->getMessage())]);
            $response->getBody()->write($payload);
            return ($response)->withStatus(409);
        } catch (\HomeDocs\Exception\UnauthorizedException $e) {
            $this->logger->debug(sprintf("Exception caught (%s) - Message: %s", get_class($e), $e->getMessage()));
            $response = new \Slim\Psr7\Response();
            $payload = json_encode([]);
            $response->getBody()->write($payload);
            return ($response)->withStatus(401);
        } catch (\HomeDocs\Exception\AccessDeniedException $e) {
            $this->logger->debug(sprintf("Exception caught (%s) - Message: %s", get_class($e), $e->getMessage()));
            $response = new \Slim\Psr7\Response();
            $payload = json_encode([]);
            $response->getBody()->write($payload);
            return ($response)->withStatus(403);
        } catch (\HomeDocs\Exception\NotFoundException $e) {
            $this->logger->debug(sprintf("Exception caught (%s) - Message: %s", get_class($e), $e->getMessage()));
            $response = new \Slim\Psr7\Response();
            $payload = json_encode(['keyNotFound' => $e->getMessage()]);
            $response->getBody()->write($payload);
            return ($response)->withStatus(404);
        } catch (\Throwable $e) {
            $this->logger->debug(sprintf("Exception caught (%s) - Message: %s", get_class($e), $e->getMessage()));
            $response = new \Slim\Psr7\Response();
            $exception = [
                'type' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getLine(),
                'line' => $e->getFile()
            ];
            $parent = $e->getPrevious();
            if ($parent) {
                $exception['parent'] = ['type' => get_class($parent), 'message' => $parent->getMessage(), 'file' => $parent->getFile(), 'line' => $parent->getLine()];
            }
            $payload = json_encode([
                'exception' => $exception
            ]);
            $response->getBody()->write($payload);
            return ($response)->withStatus(500);
        }
    }
}
