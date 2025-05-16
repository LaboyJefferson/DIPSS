<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class OrderStatuses extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'order_statuses';
    public $primaryKey = 'order_statuses';

    protected $fillable = [
        'status_name',
        'status_description'
    ];

    public $timestamps = false;
}
