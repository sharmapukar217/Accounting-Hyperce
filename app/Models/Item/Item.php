<?php


namespace App\Models\Item;
use App\Abstracts\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Item extends Model
{
    use HasFactory;
    protected $table='items';
}
