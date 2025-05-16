<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Product extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

     // If you want to change table name change 'reservation'
    protected $table = 'product';
    // Primary Key can be change here('id')
    public $primaryKey = 'product_id';
    protected $fillable = [
        'product_id',
        'image_url',
        'product_name', 
        'description',
        'category_id',
        'supplier_id',
    ];

    public $timestamps = false;

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class, 'product_supplier', 'product_id', 'supplier_id');
    }

    public function inventory()
    {
        return $this->hasOne(Inventory::class, 'product_id');
    }

    public function stock_transfer()
    {
        return $this->hasMany(StockTransfer::class, 'product_id');
    }

    public function sales() {
        return $this->hasMany(Sales::class, 'product_id');
    }

    public function order_items()
    {
        return $this->hasMany(OrderItems::class, 'product_id', 'product_id');
    }


}
