<?php
namespace App\Models;
 
use MongoDB\Laravel\Eloquent\Model;
 
class Recipe extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'recipes';
 
    protected $fillable = ['name', 'description'];
}