<?php

namespace App\Services;

use App\Models\AuditLogModel;

/**
 * Append-only audit writer untuk KAMELA.
 *
 * Service ini sengaja tidak menyediakan update/delete audit log.
 * Password, token, CSRF, cookie, dan secret selalu disamarkan sebelum JSON disimpan.
 */
class AuditService
{
    private const REDACTED = '[REDACTED]';

    private const SENSITIVE_KEYS = [
        'password',
        'password_confirmation',
        'pass',
        'pwd',
        'token',
        'access_token',
        'refresh_token',
        'authorization',
        'cookie',
        'csrf',
        'csrf_test_name',
        'secret',
        'api_key',
        'apikey',
    ];

    private AuditLogModel $model;

    public function __construct(?AuditLogModel $model = null)
    {
        $this->model = $model ?? new AuditLogModel();
    }

    /**
     * @param array<string,mixed> $event
     */
    public function record(array $event): bool
    {
        $status = strtoupper((string) ($event['status'] ?? 'SUCCESS'));
        if (!in_array($status, ['SUCCESS', 'FAILED', 'BLOCKED'], true)) {
            $status = 'SUCCESS';
        }

        $data = [
            'id_user' => $this->nullablePositiveInt($event['id_user'] ?? null),
            'actor_username' => $this->nullableText($event['actor_username'] ?? null, 50),
            'actor_name' => $this->nullableText($event['actor_name'] ?? null, 100),
            'actor_role' => $this->nullableText($event['actor_role'] ?? null, 30),
            'action' => $this->requiredText($event['action'] ?? 'UNKNOWN', 50),
            'entity_type' => $this->requiredText($event['entity_type'] ?? 'SYSTEM', 50),
            'entity_id' => $this->nullableText($event['entity_id'] ?? null, 64),
            'entity_label' => $this->nullableText($event['entity_label'] ?? null, 150),
            'description' => $this->nullableText($event['description'] ?? null, 255),
            'before_data' => $this->encodeSnapshot($event['before_data'] ?? null),
            'after_data' => $this->encodeSnapshot($event['after_data'] ?? null),
            'status' => $status,
            'ip_address' => $this->nullableText($event['ip_address'] ?? null, 45),
            'user_agent' => $this->nullableText($event['user_agent'] ?? null, 255),
            'http_method' => $this->nullableText($event['http_method'] ?? null, 10),
            'request_uri' => $this->nullableText($event['request_uri'] ?? null, 255),
        ];

        return $this->model->insert($data) !== false;
    }

    private function nullablePositiveInt(mixed $value): ?int
    {
        $int = (int) $value;
        return $int > 0 ? $int : null;
    }

    private function requiredText(mixed $value, int $max): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            $value = 'UNKNOWN';
        }
        return mb_substr($value, 0, $max);
    }

    private function nullableText(mixed $value, int $max): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        return mb_substr($value, 0, $max);
    }

    private function encodeSnapshot(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $sanitized = $this->sanitize($value);
        $encoded = json_encode(
            $sanitized,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
        );

        return $encoded === false ? null : $encoded;
    }

    private function sanitize(mixed $value, ?string $key = null): mixed
    {
        if ($key !== null && $this->isSensitiveKey($key)) {
            return self::REDACTED;
        }

        if (is_array($value)) {
            $clean = [];
            foreach ($value as $childKey => $childValue) {
                $childKeyString = is_string($childKey) ? $childKey : null;
                $clean[$childKey] = $this->sanitize($childValue, $childKeyString);
            }
            return $clean;
        }

        if (is_object($value)) {
            return $this->sanitize((array) $value);
        }

        if (is_string($value) && mb_strlen($value) > 2000) {
            return mb_substr($value, 0, 2000) . '…';
        }

        return $value;
    }

    private function isSensitiveKey(string $key): bool
    {
        $normalized = strtolower(trim($key));
        foreach (self::SENSITIVE_KEYS as $sensitive) {
            if ($normalized === $sensitive || str_contains($normalized, $sensitive)) {
                return true;
            }
        }
        return false;
    }
}
