<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Services\AffiliateService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {
    }

    /**
     * Pass the necessary data to the process order method
     * 
     * @param  Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        $merchant = Merchant::where('domain', $request->merchant_domain)->first();

        $data = [
            'order_id'        => $request->order_id,
            'discount_code'   => $request->discount_code,
            'subtotal_price'  => $request->subtotal_price,
            'merchant_domain' => $request->merchant_domain
        ];

        $this->orderService->processOrder($data);

        return response()->json($data, 200);
    }
}
