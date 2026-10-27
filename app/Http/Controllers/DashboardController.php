<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Admin melihat semua transaksi, staff hanya miliknya sendiri.
        $sales = Sale::completed()->when(! $user->isAdmin(), fn ($q) => $q->where('user_id', $user->id));
        $purchases = Purchase::where('status', 'completed')
            ->when(! $user->isAdmin(), fn ($q) => $q->where('user_id', $user->id));

        $stats = [
            'total_products' => Product::count(),
            'low_stock' => Product::lowStock()->count(),
            'sales_count' => (clone $sales)->count(),
            'purchases_count' => (clone $purchases)->count(),
            'sales_today' => (clone $sales)->whereDate('sold_at', today())->sum('total'),
            'sales_month' => (clone $sales)
                ->whereYear('sold_at', now()->year)
                ->whereMonth('sold_at', now()->month)
                ->sum('total'),
        ];

        $lowStockProducts = Product::with('category')->lowStock()->orderBy('stock')->limit(5)->get();
        $recentSales = (clone $sales)->with('user')->latest('sold_at')->limit(5)->get();

        return view('dashboard', compact('stats', 'lowStockProducts', 'recentSales'));
    }
}
