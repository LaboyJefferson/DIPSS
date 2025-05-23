<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Address extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'address';
    public $primaryKey = 'address_id';

    protected $fillable = [
        'address_id',
        'address'
    ];

    public $timestamps = false;
}