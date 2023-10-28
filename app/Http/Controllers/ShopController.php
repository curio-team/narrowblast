<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ShopController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('app.shop.index');
    }

    /**
     * Displays the users inventory
     */
    public function inventory()
    {
        $items = auth()->user()->purchasedShopItems;

        return view('app.shop.inventory', compact('items'));
    }

    /**
     * Display the credits a user owns, and allow them to claim more with a code
     */
    public function credits()
    {
        $credits = auth()->user()->getFormattedCredits();

        return view('app.shop.credits', compact('credits'));
    }
}
