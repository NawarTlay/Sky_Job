<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeProjectes extends Model
{
    use HasFactory;

    protected $fillable = [
        'link',
        'description',
        'skiils',
        'employee_id',
    ];
}
