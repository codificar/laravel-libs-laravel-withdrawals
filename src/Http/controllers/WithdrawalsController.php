<?php

namespace Codificar\Withdrawals\Http\Controllers;

use Codificar\Withdrawals\Models\Withdrawals;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;



class WithdrawalsController extends Controller {

    public function index()
    {
       return view('withdrawals::contact');
    }

    public function sendMail(Request $request)
    {
        Withdrawals::create($request->all());
        return redirect(route('contact'))->with(['message' => 'Thank you, your mail has been sent succesfully.']);
    }


}