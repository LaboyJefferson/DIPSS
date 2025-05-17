<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class InventoryAudit extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

     // If you want to change table name change 'reservation'
    protected $table = 'inventory_audit';
    // Primary Key can be change here('id')
    public $primaryKey = 'audit_id';
    protected $fillable = [
        'audit_id',
        'physical_storestock_count',
        'system_storestock_record',
        'storestock_discrepancies',
        'physical_stockroom_count',
        'system_stockroom_record',
        'stockroom_discrepancies',
        'in_stock_discrepancies',
        'discrepancy_reason',
        'audit_date',
        'inventory_id',
        'user_id',
    ];

    public $timestamps = false;

    public function inventory() {
        return $this->belongsTo(Inventory::class, 'inventory_id');
    }
    
    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

}
