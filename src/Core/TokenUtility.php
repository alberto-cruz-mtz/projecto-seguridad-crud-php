<?php

declare(strict_types=1);

namespace Tito\CrudUsers\Core;

use JsonException;

final class TokenUtility
{
    public function __construct(private string $secretKey)
    {
    }

    /** @param array<string, mixed> $payload */
    public function generate(array $payload, int $expiresInSeconds): string
    {
        $payload['exp'] = time() + $expiresInSeconds;

        $encodedPayload = $this->base64UrlEncode($this->encodeJson($payload));
        $signature = hash_hmac('sha256', $encodedPayload, $this->secretKey, true);
        $encodedSignature = $this->base64UrlEncode($signature);

        return $encodedPayload . '.' . $encodedSignature;
    }

    /** @return array<string, mixed>|null */
    public function validateAndDecode(string $token): ?array
    {
        if ($token === '') {
            return null;
        }

        $parts = explode('.', $token);
        if (count($parts) !== 2) {
            return null;
        }

        [$encodedPayload, $encodedSignature] = $parts;

        $expectedSignature = $this->base64UrlEncode(
            hash_hmac('sha256', $encodedPayload, $this->secretKey, true)
        );

        if (!hash_equals($expectedSignature, $encodedSignature)) {
            return null;
        }

        $payloadJson = $this->base64UrlDecode($encodedPayload);
        if ($payloadJson === null) {
            return null;
        }

        try {
            $payload = json_decode($payloadJson, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return null;
        }

        if (!is_array($payload)) {
            return null;
        }

        $expiresAt = (int) ($payload['exp'] ?? 0);
        if ($expiresAt < time()) {
            return null;
        }

        return $payload;
    }

    /** @param array<string, mixed> $payload */
    private function encodeJson(array $payload): string
    {
        try {
            return json_encode($payload, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return '{}';
        }
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $value): ?string
    {
        $padding = strlen($value) % 4;
        if ($padding > 0) {
            $value .= str_repeat('=', 4 - $padding);
        }

        $decoded = base64_decode(strtr($value, '-_', '+/'), true);

        if ($decoded === false) {
            return null;
        }

        return $decoded;
    }
}
