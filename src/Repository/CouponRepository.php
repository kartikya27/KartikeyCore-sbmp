<?php

namespace Kartikey\Core\Repository;

use Kartikey\Core\Eloquent\Repository;
use Kartikey\Core\Models\Coupon;
use Stegback\Checkout\Facades\Cart;

class CouponRepository extends Repository
{

    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return Coupon::class;

    }

    /**
     * Apply the coupon and calculate the new total amount.
     */
    public function applyCoupon($coupon)
    {

        try {
            // Fetch cart details
            $cart = Cart::getCart();
            Cart::setCart($cart);

            if (!$cart || !isset($cart['grand_total'])) {
                throw new \Exception("Cart data or grand total is not available.");
            }

            Cart::setCouponCode($coupon['code'])->collectTotals();

            return true;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Create a new coupon.
     */
    public function createUpdateCoupon(array $data)
    {
        try {
            if (isset($data['rules']) && is_array($data['rules'])) {
                $data['rules'] = json_encode($data['rules']);
            }

            // Check if the coupon exists with the same seller_id and is soft deleted
            $existingCoupon = $this->model()::withTrashed()
                ->where('code', $data['code'])
                ->where('seller_id', $data['seller_id'])
                ->first();

            if ($existingCoupon && $existingCoupon->trashed()) {
                // Restore the soft deleted coupon
                $existingCoupon->restore();
                $existingCoupon->update($data);
                return $existingCoupon;
            }

            // No need to block coupon creation for a different seller_id
            $coupon = $this->model()::updateOrCreate(
                ['code' => $data['code'], 'seller_id' => $data['seller_id']],
                $data
            );

            return $coupon;
        } catch (\Exception $e) {
            throw new \Exception("Failed to create or update coupon: " . $e->getMessage());
        }
    }

}
