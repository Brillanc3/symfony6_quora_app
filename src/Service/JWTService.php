<?php

namespace App\Service;

class JWTService
{
    /**
     * Génération du JWT
     * @param array $header
     * @param array $playload
     * @param string $secret
     * @param int $validity
     * @return string
     */
    public function generateToken(array $header, array $playload, string $secret, int $validity = 300): string
    {

        if ($validity > 0) {
            $now = new \DateTimeImmutable();
            $expiration = $now->getTimestamp() + $validity;
    
            $playload['iat'] = $now->getTimestamp();
            $playload['exp'] = $expiration;
        }


        $base64Header = base64_encode(json_encode($header));
        $base64Payload = base64_encode(json_encode($playload));

        $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], $base64Header);
        $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], $base64Payload);

        $secret = base64_encode($secret);

        $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, $secret, true);

        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        $jswt = $base64Header . "." . $base64Payload . "." . $base64Signature;

        return $jswt;
    }

    /**
     * Vérification du JWT
     * @param string $token
     * @return bool
     */
    public function isValide(string $token): bool
    {
        return preg_match('/^[a-zA-Z0-9\-\_\=]+\.[a-zA-Z0-9\-\_\=]+\.[a-zA-Z0-9\-\_\=]+$/', $token) === 1;
    }

    /**
     * Récupération du Payload
     */
    public function getPayload(string $token): array
    {
        $payload = explode('.', $token)[1];
        $payload = json_decode(base64_decode($payload), true);
        return $payload;
    }

    /**
     * Récupération de l'entête
     */
    public function getHeader(string $token): array
    {
        $header = explode('.', $token)[0];
        $header = json_decode(base64_decode($header), true);
        return $header;
    }

    /**
     * Vérification de l'expiration du token
     */
    public function isExpired(string $token): bool
    {
        $payload = $this->getPayload($token);
        $now = new \DateTimeImmutable();
        return $now->getTimestamp() > $payload['exp'];
    }

    /**
     * Vérification de la signature
     */
    public function isSignatureValid(string $token, string $secret): bool
    {
        $header = $this->getHeader($token);
        $payload = $this->getPayload($token);

        $verifToken = $this->generateToken($header, $payload, $secret, 0);

        return $verifToken === $token;
    }
}
