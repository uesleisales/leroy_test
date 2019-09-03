<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    
     
    protected $fillable = [
        'lm', 
        'name', 
        'free_shipping',
        'description',
        'category',
        'price',
        'cdPlan',
    ];
}
