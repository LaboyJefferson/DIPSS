<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class OrderItems extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'order_items';
    public $primaryKey = 'order_items_id';

    protected $fillable = [
        'order_items_id',
        'quantity',
        'price', 
        'delivered_quantity',
        'purchase_order_id',
        'product_id',
    ];

    public $timestamps = false;

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
}
