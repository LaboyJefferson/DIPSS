<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Stockroom extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

     // If you want to change table name change 'reservation'
    protected $table = 'stockroom';
    // Primary Key can be change here('id')
    public $primaryKey = 'stockroom_id';
    protected $fillable = [
        'stockroom_id',
        'aisle_number', 
        'cabinet_level',
        'product_quantity',
        'category_id',
    ];

    public $timestamps = false;

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function stock_transfer()
    {
        return $this->hasMany(StockTransfer::class, 'stockroom_id');
    }

    public function updateQuantity($amount)
    {
        $this->product_quantity += $amount;
        $this->save();
    }

}
