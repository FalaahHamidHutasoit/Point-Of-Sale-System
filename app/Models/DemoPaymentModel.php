<?php

namespace App\Models;

use CodeIgniter\Model;

class DemoPaymentModel extends Model
{
    protected $table = 'demo_payments';
    protected $primaryKey = 'id_demo_payment';
    protected $allowedFields = [
        'token', 'method', 'payment_reference', 'id_user', 'id_customer', 'amount', 'payload', 'status',
        'expires_at', 'paid_at', 'cancelled_at', 'id_penjualan', 'created_at',
    ];
    protected $useTimestamps = false;
}
