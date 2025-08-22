<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Catched;
use Illuminate\Support\Carbon;
use App\Http\Requests\FilterRequest;
use Illuminate\Support\Facades\Auth;

class PublicController extends Controller
{
    public function index(){
        return Inertia::render('Public/Home/Home'); 
    }

    public function pricing(){
        return Inertia::render('Public/Pricing/Pricing'); 
    }

    public function contact(){
        return Inertia::render('Public/Contact/Contact'); 
    }

    

    public function showCatched(Catched $catch)
    {
        if($catch == null)
            abort(404);

        $userName = $catch->user->name;

        return Inertia::render('Public/Catch/Show', [
            'catched' => $catch->load('media'),
            'user' => $userName
        ]);
    }
}
