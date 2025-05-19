<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Inventory extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $table = 'inventory';
    public $primaryKey = 'inventory_id';
    
    protected $fillable = [
        'inventory_id',
        'purchase_price_per_unit', 
        'sale_price_per_unit', 
        'unit_of_measure', 
        'in_stock',
        'reorder_level',
        'updated_at',
        'product_id',
        'profit_margin',    // Added
        'tax_rate'          // Added
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
    
    public function stockroom()
    {
        return $this->belongsTo(Stockroom::class, 'stockroom_id');
    }
    
    public function stock_transfer()
    {
        return $this->belongsTo(StockTransfer::class, 'stock_transfer_id');
    }
    
    public function audits() {
        return $this->hasMany(InventoryAudit::class, 'audit_id');
    }
}