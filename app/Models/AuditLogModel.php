<?php

namespace App\Models;

use CodeIgniter\Model;

class AuditLogModel extends Model
{
    protected $table = 'audit_logs';
    protected $primaryKey = 'id_audit';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;

    protected $allowedFields = [
        'id_user',
        'actor_username',
        'actor_name',
        'actor_role',
        'action',
        'entity_type',
        'entity_id',
        'entity_label',
        'description',
        'before_data',
        'after_data',
        'status',
        'ip_address',
        'user_agent',
        'http_method',
        'request_uri',
    ];
}
