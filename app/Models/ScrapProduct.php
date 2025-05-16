<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScrapProduct extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $table = 'scrap_product';
    public $primaryKey = 'scrap_product_id';
    protected $fillable = [
        'scrap_product_id',
        'scrap_quantity',
        'scrap_date',
        'user_id',
    ];

    public $timestamps = false;

    public function user(){ 
        return $this->belongsTo(User::class, 'user_id');
    }

    public function returnProduct()
    {
        return $this->hasOne(ReturnProduct::class, 'scrap_product_id');
    }
}
