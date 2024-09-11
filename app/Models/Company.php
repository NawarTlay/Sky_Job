<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $table = "company";

    protected $fillable = [
        'profession',
        'services',
        'description',
        'user_id',
    ];

    public function postsCompany(){
        return $this->hasMany(Jobs::class);
    }
    
} 
