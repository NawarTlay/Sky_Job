<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $table = "employee";

    protected $fillable = [
        'skills',
        'university',
        'user_id',
    ];

    public function employeeProjectes(){
        return $this->hasMany(EmployeeProjectes::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class); // Ensures that each employee can have multiple orders
    }

}
