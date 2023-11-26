<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class OrderService
{
    public function __construct(
        protected AffiliateService $affiliateService
    ) {
    }

    /**
     * Process an order and log any commissions.
     * This should create a new affiliate if the customer_email is not already associated with one.
     * This method should also ignore duplicates based on order_id.
     *
     * @param  array{order_id: string, subtotal_price: float, merchant_domain: string, discount_code: string, customer_email: string, customer_name: string} $data
     * @return void
     */
    public function processOrder(array $data)
    {
        $affiliate = Affiliate::whereHas('user', function ($query) use ($data) {
            $query->where("email", $data['customer_email']);
        })->first();

        $merchant = Merchant::where('domain', $data['merchant_domain'])->first();

        if (!$affiliate) {

            $user = User::create([
                'email' => $data['customer_email'],
                'name' => $data['customer_name'],
                'type' => User::TYPE_AFFILIATE
            ]);

            $affiliate = $user->affiliate()->create(
                [
                    'merchant_id'     => $merchant->id,
                    'discount_code'   => $data['discount_code'],
                    'commission_rate' => $merchant->default_commission_rate,
                ]
            );
        }

        $order = new Order([
            'subtotal' => $data['subtotal_price'],
            'merchant_id' => $merchant->id,
            'affiliate_id' => $affiliate->id,
            'payout_status' => Order::STATUS_PAID,
            'discount_code' => $data['discount_code'],
            'commission_owed' => $data['subtotal_price'] * $affiliate->commission_rate,
        ]);

        Log::info("Order Commision: $order->commission_owed");
    }
}
