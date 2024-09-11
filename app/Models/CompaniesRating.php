<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompaniesRating extends Model
{
    use HasFactory;

    protected $fillable = ['company_id', 'employee_id', 'rating'];

    public function Company(){
        return $this->belongsTo(Company::class);
    }

    public function Employee(){
        return $this->belongsTo(Employee::class);
    }
}
