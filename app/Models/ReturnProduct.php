<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class ReturnProduct extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

     // If you want to change table name change 'reservation'
    protected $table = 'return_product';
    // Primary Key can be change here('id')
    public $primaryKey = 'return_product_id';
    protected $fillable = [
        'return_product_id',
        'return_quantity', 
        'total_return_amount',
        'return_reason',
        'return_date',
        'scrap_product_id',
        'user_id',
    ];

    public $timestamps = false; // false = to enable customization on timestamp at DB, true = automatic timestamp
    public function user(){ 
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scrapProduct(){ 
        return $this->belongsTo(ScrapProduct::class, 'scrap_product_id');
    }
}
