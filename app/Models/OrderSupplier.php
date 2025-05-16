<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class OrderSupplier extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'order_supplier';
    public $primaryKey = 'order_supplier_id';

    protected $fillable = [
        'order_supplier_id',
        'purchase_order_id',
        'supplier_id',
    ];

    public $timestamps = false;
}
