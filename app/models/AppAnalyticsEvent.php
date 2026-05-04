<?php

namespace App\Models;

use Classes\Model;

class AppAnalyticsEvent extends Model
{
    protected string $table = 'app_analytics_events';

    public function logEvent(array $data): int|false
    {
        return $this->insert([
            'patient_id' => (int) ($data['patient_id'] ?? 0),
            'user_id' => (int) ($data['user_id'] ?? 0),
            'platform' => (string) ($data['platform'] ?? 'unknown'),
            'event_name' => (string) ($data['event_name'] ?? 'unknown_event'),
            'event_context' => (string) ($data['event_context'] ?? ''),
            'payload_json' => (string) ($data['payload_json'] ?? '{}'),
            'created_at' => (string) ($data['created_at'] ?? date('Y-m-d H:i:s')),
        ]);
    }
}