<?php

namespace App\Models;

use Classes\Model;

class TaskAttachment extends Model
{
    protected string $table = 'task_attachments';

    public function findByTask(int $taskId): array
    {
        return $this->find('task_id = ?', [$taskId], 'id DESC');
    }
}
