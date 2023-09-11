<?php
namespace App\Http\Controllers\Modals;

use App\Abstracts\Http\Controller;

use App\Models\Item\Item;


class Invoicereport extends Controller
{
    public function view()
    {
       $data=Item::all();
       //dd($data);   
       return view('modals.customers.create');
       
}
    }

