<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class SalesDetails extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

     // If you want to change table name change 'reservation'
    protected $table = 'sales_details';
    // Primary Key can be change here('id')
    public $primaryKey = 'sales_details_id';
    protected $fillable = [
        'sales_details_id',
        'sales_quantity',
        'amount',
        'sales_id', 
        'inventory_id',
        'product_id',
        'return_product_id',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function return_product() {
        return $this->hasMany(ReturnProduct::class, 'return_product_id');
    }

    public function sales() {
        return $this->belongsTo(Sales::class, 'sales_id');
    }

    public function inventory() {
        return $this->belongsTo(Inventory::class, 'inventory_id');
    }
}
