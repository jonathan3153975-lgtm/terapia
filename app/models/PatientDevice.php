<?php

namespace App\Models;

use Classes\Model;

class PatientDevice extends Model
{
    protected string $table = 'patient_devices';

    public function upsertDevice(array $data): bool
    {
        $stmt = $this->query(
            'INSERT INTO patient_devices (
                patient_id, user_id, platform, device_name, device_identifier,
                push_token, app_version, locale, last_seen_at, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                user_id = VALUES(user_id),
                device_name = VALUES(device_name),
                push_token = VALUES(push_token),
                app_version = VALUES(app_version),
                locale = VALUES(locale),
                last_seen_at = VALUES(last_seen_at),
                updated_at = VALUES(updated_at)',
            [
                (int) ($data['patient_id'] ?? 0),
                (int) ($data['user_id'] ?? 0),
                (string) ($data['platform'] ?? 'android'),
                (string) ($data['device_name'] ?? ''),
                (string) ($data['device_identifier'] ?? ''),
                (string) ($data['push_token'] ?? ''),
                (string) ($data['app_version'] ?? ''),
                (string) ($data['locale'] ?? 'pt-BR'),
                (string) ($data['last_seen_at'] ?? date('Y-m-d H:i:s')),
                (string) ($data['created_at'] ?? date('Y-m-d H:i:s')),
                (string) ($data['updated_at'] ?? date('Y-m-d H:i:s')),
            ]
        );

        return (bool) $stmt;
    }
}