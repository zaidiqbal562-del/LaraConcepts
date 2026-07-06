<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    /**
     * Display a simple list of products (hardcoded).
     */
    public function index()
    {
        $products = Product::all();

        return view('products.index', compact('products'));
    }
}
