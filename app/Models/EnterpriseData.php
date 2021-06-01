<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class EnterpriseData extends Model
{
    protected $table = 'enterprise_data';
    protected $fillable = ['*'];
    public $timestamps= false;
}