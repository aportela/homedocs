<?php

declare(strict_types=1);

namespace HomeDocs;

class JWT
{
    public const ALGORITHM = 'HS256';

    public function __construct(private readonly \Psr\Log\LoggerInterface $logger, private readonly string $passphrase)
    {
        $this->logger->debug("JWT passphrase", [$this->passphrase]);
    }

    public function encode(mixed $payload, int $expiresAt): string
    {
        $jwt = "";
        $this->logger->notice("JWT encoding", [$payload]);
        try {
            $issuedAt = time();
            $jwtPayload = [
                'iat' => $issuedAt,
                'data' => $payload,
            ];
            if ($expiresAt > 0) {
                $jwtPayload['exp'] = $expiresAt;
            }
            $jwt = \Firebase\JWT\JWT::encode(
                $jwtPayload,
                $this->passphrase,
                self::ALGORITHM
            );
        } catch (\Throwable $throwable) {
            $this->logger->error("JWT encoding error", [$throwable->getMessage()]);
        } finally {
            return ($jwt);
        }
    }

    public function decode(string $jwt): \stdClass
    {
        $data = new \stdClass();
        $this->logger->notice("JWT decoding", [$jwt]);
        $data = \Firebase\JWT\JWT::decode($jwt, new \Firebase\JWT\Key($this->passphrase, self::ALGORITHM));
        $this->logger->debug("Decoded JWT ", [$data]);
        return ($data);
    }
}
