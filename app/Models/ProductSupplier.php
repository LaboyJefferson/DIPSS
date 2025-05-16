<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class ProductSupplier extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'product_supplier';
    public $primaryKey = 'product_supplier_id';

    protected $fillable = [
        'product_supplier_id',
        'product_id',
        'supplier_id',
    ];

    public $timestamps = false;
}
