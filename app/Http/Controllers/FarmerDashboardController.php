<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class FarmerDashboardController extends Controller
{
    public function index(Request $request)
    {
        // শুধু Farmer Dashboard খুলতে পারবে
        if ($request->user()->role !== 'farmer') {
            
            abort(403, 'শুধুমাত্র Farmer এই Dashboard ব্যবহার করতে পারবেন।');
        }

     $products = $request->user()->products()->latest()->get();

        return view('farmer.dashboard', compact('products'));
    }
}