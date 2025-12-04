<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class VirtualHost extends Model
{
    protected $fillable = ['domain', 'port', 'conf_file'];
}
