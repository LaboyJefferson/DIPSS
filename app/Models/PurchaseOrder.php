<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class PurchaseOrder extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'purchase_order';
    public $primaryKey = 'purchase_order_id';

    protected $fillable = [
        'purchase_order_id',
        'type',
        'payment_method',
        'billing_address',
        'shipping_address',
        'total_price',
        'reason',
        'created_by',
        'supplier_id',
        'order_status',
        'created_at',
        'updated_at'
    ];

    public $timestamps = false;

    public function order_items()
    {
        return $this->hasMany(OrderItems::class, 'purchase_order_id', 'purchase_order_id');
    }

    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class, 'order_supplier', 'purchase_order_id', 'supplier_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    public function status()
    {
        return $this->belongsTo(OrderStatuses::class, 'order_status', 'order_statuses');
    }
}