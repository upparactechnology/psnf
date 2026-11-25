<?php

declare(strict_types=1);

namespace Core;

class JWT
{
    private static string $algorithm = 'HS256';

    public static function encode(array $payload, ?string $secret = null): string
    {
        $secret = $secret ?? self::secret();

        $header  = base64url_encode(json_encode(['alg' => self::$algorithm, 'typ' => 'JWT']));
        $payload['iat'] = $payload['iat'] ?? time();
        $payload['exp'] = $payload['exp'] ?? (time() + config('auth.jwt_ttl', 2592000)); // 30 days

        $payloadEncoded = base64url_encode(json_encode($payload));
        $signature      = self::sign("$header.$payloadEncoded", $secret);

        return "$header.$payloadEncoded.$signature";
    }

    public static function decode(string $token, ?string $secret = null): array|false
    {
        $secret = $secret ?? self::secret();

        $parts = explode('.', $token);
        if (count($parts) !== 3) return false;

        [$header, $payload, $signature] = $parts;

        $hdr = json_decode(base64url_decode($header), true);
        if (!is_array($hdr) || ($hdr['alg'] ?? '') !== self::$algorithm) return false;

        // Verify signature
        $expectedSig = self::sign("$header.$payload", $secret);
        if (!hash_equals($expectedSig, $signature)) return false;

        $decoded = json_decode(base64url_decode($payload), true);
        if (!$decoded) return false;

        // Check expiry
        if (isset($decoded['exp']) && $decoded['exp'] < time()) {
            return false;
        }

        return $decoded;
    }

    public static function refresh(string $token, ?string $secret = null): string|false
    {
        $decoded = self::decode($token, $secret);
        if (!$decoded) return false;

        unset($decoded['iat'], $decoded['exp']);
        return self::encode($decoded, $secret);
    }

    private static function secret(): string
    {
        $secret = (string) config('auth.jwt_secret', '');
        if (strlen($secret) < 32) {
            throw new \RuntimeException('JWT secret is not configured (set JWT_SECRET or storage/keys/jwt_secret).');
        }
        return $secret;
    }

    private static function sign(string $data, string $secret): string
    {
        return base64url_encode(hash_hmac('sha256', $data, $secret, true));
    }
}

function base64url_encode(string $data): string
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode(string $data): string
{
    return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 4 - strlen($data) % 4));
}
