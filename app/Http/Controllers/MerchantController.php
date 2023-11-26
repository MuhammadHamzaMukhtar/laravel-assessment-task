<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Models\Order;
use App\Services\MerchantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MerchantController extends Controller
{
    public function __construct(
        MerchantService $merchantService
    ) {
    }

    /**
     * Useful order statistics for the merchant API.
     * 
     * @param Request $request Will include a from and to date
     * @return JsonResponse Should be in the form {count: total number of orders in range, commission_owed: amount of unpaid commissions for orders with an affiliate, revenue: sum order subtotals}
     */
    public function orderStats(Request $request): JsonResponse
    {
        $orders = Order::whereBetween("created_at", [$request->from, $request->to]);
        $totalOrders = $orders->count();

        $unpaidCommissionsAmount = $orders->whereHas('affiliate')
            ->where('payout_status', Order::STATUS_UNPAID)
            ->sum('commission_owed');

        $revenue = Order::whereBetween("created_at", [$request->from, $request->to])->sum('subtotal');

        $response = [
            'count'             => $totalOrders,
            'commissions_owed'  => $unpaidCommissionsAmount,
            'revenue'           => $revenue
        ];

        return response()->json($response);
    }
}
