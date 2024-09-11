<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jobs extends Model
{
    use HasFactory;

    protected $fillable = [
        'jobName',
        'salary',
        'deadline',
        'description',
        'company_id',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class); // Each job can be associated with multiple orders
    }
}
