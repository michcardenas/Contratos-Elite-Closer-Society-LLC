<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalePaymentProof extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'file_path',
        'original_name',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
}
