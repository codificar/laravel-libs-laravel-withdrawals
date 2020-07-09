<?php

namespace Codificar\Generic\Http\Controllers;

use Codificar\Generic\Models\Generic;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;



class GenericController extends Controller {

    public function index()
    {
       return view('generic::contact');
    }

    public function sendMail(Request $request)
    {
        Generic::create($request->all());
        return redirect(route('contact'))->with(['message' => 'Thank you, your mail has been sent succesfully.']);
    }


}