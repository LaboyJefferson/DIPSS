<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
class Delivery extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'delivery';
    public $primaryKey = 'delivery_id';

    protected $fillable = [
        'delivery_id',
        'issued_date',
        'date_delivered',
        'purchase_order_id'
    ];

    public $timestamps = false;
}
