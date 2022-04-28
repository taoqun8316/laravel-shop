<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;

class ProductSku extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'price', 'stock'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function addStock($amount)
    {
        if ($amount>0){
            $this->increment("stock", $amount);
        }
    }

    public function decreaseStock($amount)
    {
        if ($amount>0){
            return $this->where("id", $this->id)->where("stock", ">=", $amount)->decrement("stock", $amount);
        }
    }

}
