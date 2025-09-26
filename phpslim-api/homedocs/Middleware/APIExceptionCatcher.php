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
    private function handleException(\Exception $e, int $statusCode, array $payload): \Psr\Http\Message\ResponseInterface
    {
        $this->logger->debug(sprintf("Exception caught (%s) - Message: %s", get_class($e), $e->getMessage()));
        $response = new \Slim\Psr7\Response();
        $response->getBody()->write(json_encode($payload));
        return $response->withStatus($statusCode);
    }

    private function handleGenericException(\Throwable $e): \Psr\Http\Message\ResponseInterface
    {
        $this->logger->error(sprintf("Unhandled exception caught (%s) - Message: %s", get_class($e), $e->getMessage()));
        $this->logger->debug($e->getTraceAsString());
        $response = new \Slim\Psr7\Response();
        $exception = [
            'type' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ];
        $parent = $e->getPrevious();
        if ($parent) {
            $exception['parent'] = [
                'type' => get_class($parent),
                'message' => $parent->getMessage(),
                'file' => $parent->getFile(),
                'line' => $parent->getLine()
            ];
        }
        $payload = json_encode([
            'exception' => $exception,
            'status' => 500
        ]);
        $response->getBody()->write($payload);
        return $response->withStatus(500);
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
            return $handler->handle($request);
        } catch (\HomeDocs\Exception\InvalidParamsException $e) {
            return $this->handleException($e, 400, ['invalidOrMissingParams' => explode(",", $e->getMessage())]);
        } catch (\HomeDocs\Exception\AlreadyExistsException $e) {
            return $this->handleException($e, 409, ['invalidOrMissingParams' => explode(",", $e->getMessage())]);
        } catch (\HomeDocs\Exception\UnauthorizedException $e) {
            return $this->handleException($e, 401, []);
        } catch (\HomeDocs\Exception\AccessDeniedException $e) {
            return $this->handleException($e, 403, []);
        } catch (\HomeDocs\Exception\NotFoundException $e) {
            return $this->handleException($e, 404, ['keyNotFound' => $e->getMessage()]);
        } catch (\Throwable $e) {
            return $this->handleGenericException($e);
        }
    }
}
