<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Member;
use App\Models\Shift;
use App\Models\Stock;
use App\Models\Promotion;
use App\Models\GiftCard;
use App\Models\CompanySetting;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CashierController extends Controller
{
    public function index()
    {
        $products = Product::where('is_active', true)
            ->with(['category', 'unit', 'stocks' => function ($q) {
                $q->select('product_id', DB::raw('SUM(quantity) as total_stock'))
                    ->groupBy('product_id');
            }])
            ->orderBy('name')
            ->get();

        $currentShift = Shift::where('user_id', auth()->id())
            ->where('status', 'open')
            ->first();

        $promotions = Promotion::where('is_active', true)
            ->where('start_date', '<=', Carbon::now())
            ->where('end_date', '>=', Carbon::now())
            ->with('products')
            ->get();

        $promoByProduct = [];
        foreach ($promotions as $promo) {
            foreach ($promo->products as $pp) {
                $promoByProduct[$pp->product_id] = [
                    'type' => $promo->type,
                    'value' => $promo->value,
                    'name' => $promo->name,
                ];
            }
        }

        $company = CompanySetting::instance();
        $companyData = json_encode($company->only(['company_name', 'address', 'phone', 'logo', 'receipt_header', 'receipt_footer', 'receipt_message']));

        return view('pos.cashier', compact('products', 'currentShift', 'promoByProduct', 'company', 'companyData'));
    }

    public function searchProduct(Request $request)
    {
        $request->validate([
            'query' => 'required|string',
        ]);

        $search = $request->input('query');

        $products = Product::where('is_active', true)
            ->where(function ($q) use ($search) {
                $q->where('barcode', $search)
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            })
            ->with(['category', 'unit', 'stocks' => function ($q) {
                $q->select('product_id', DB::raw('SUM(quantity) as total_stock'))
                    ->groupBy('product_id');
            }])
            ->limit(20)
            ->get();

        return response()->json($products);
    }

    public function processSale(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'member_id' => 'nullable|exists:members,id',
            'points_redeemed' => 'nullable|integer|min:0',
            'payment_method' => 'required|in:cash,card,transfer,ewallet,mixed',
            'amount_paid' => 'required|numeric|min:0',
            'shift_id' => 'required|exists:shifts,id',
            'gift_card_code' => 'nullable|string|exists:gift_cards,code',
            'gift_card_amount' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $member = null;
            $memberDiscountPercent = 0;
            if ($request->member_id) {
                $member = Member::find($request->member_id);
                $memberDiscountPercent = $member ? $member->getDiscountPercent() : 0;
            }

            $totalAmount = 0;
            $totalDiscount = 0;

            foreach ($request->items as $item) {
                $totalAmount += $item['price'] * $item['quantity'];
            }

            $promotions = Promotion::where('is_active', true)
                ->where('start_date', '<=', Carbon::now())
                ->where('end_date', '>=', Carbon::now())
                ->with('products')
                ->get();

            foreach ($promotions as $promo) {
                if ($promo->type === 'discount_percent') {
                    foreach ($request->items as $item) {
                        if ($promo->products->pluck('product_id')->contains($item['product_id'])) {
                            $discount = ($item['price'] * $item['quantity']) * ($promo->value / 100);
                            $totalDiscount += $discount;
                        }
                    }
                } elseif ($promo->type === 'discount_amount') {
                    foreach ($request->items as $item) {
                        if ($promo->products->pluck('product_id')->contains($item['product_id'])) {
                            $totalDiscount += $promo->value;
                        }
                    }
                }
            }

            $finalAmount = max(0, $totalAmount - $totalDiscount);

            $memberDiscountAmount = 0;
            if ($memberDiscountPercent > 0) {
                $memberDiscountAmount = round($finalAmount * $memberDiscountPercent / 100);
                $finalAmount -= $memberDiscountAmount;
            }

            $pointsDiscount = 0;
            $pointsRedeemed = 0;
            if ($member && $request->filled('points_redeemed') && $request->points_redeemed > 0) {
                $pointsRedeemed = min((int) $request->points_redeemed, (int) $member->points);
                $pointsDiscount = $pointsRedeemed * $member->getPointsValue();
                $finalAmount = max(0, $finalAmount - $pointsDiscount);
            }

            $finalAmount = max(0, $finalAmount);

            $giftCard = null;
            $giftCardAmount = 0;
            if ($request->filled('gift_card_code')) {
                $giftCard = GiftCard::active()->valid()->where('code', $request->gift_card_code)->first();
                if (!$giftCard) {
                    throw new \Exception('Gift card tidak valid atau saldo habis');
                }
                $giftCardAmount = min((float) $request->gift_card_amount, $giftCard->current_balance, $finalAmount);
            }

            $remainingFinal = $finalAmount - $giftCardAmount;

            $taxAmount = round($remainingFinal * 0.11);
            $grandTotal = $remainingFinal + $taxAmount;

            $paymentMethod = $request->payment_method;
            if ($giftCard && $giftCardAmount > 0) {
                $paymentMethod = $paymentMethod === 'cash' && $request->amount_paid <= 0 ? 'gift_card' : 'mixed';
            }

            $shift = Shift::findOrFail($request->shift_id);

            $sale = Sale::create([
                'code' => $this->generateSaleCode(),
                'cash_register_id' => $shift->cash_register_id,
                'shift_id' => $request->shift_id,
                'member_id' => $request->member_id,
                'user_id' => auth()->id(),
                'sale_date' => Carbon::today(),
                'subtotal' => $totalAmount,
                'discount_amount' => $totalDiscount + $memberDiscountAmount,
                'member_discount' => $memberDiscountAmount,
                'tax_amount' => $taxAmount,
                'total' => $grandTotal,
                'payment_method' => $paymentMethod,
                'amount_paid' => $request->amount_paid,
                'change_amount' => max(0, $request->amount_paid - $grandTotal),
                'status' => 'completed',
                'points_earned' => 0,
                'points_redeemed' => $pointsRedeemed,
                'points_discount' => $pointsDiscount,
                'gift_card_id' => $giftCard?->id,
                'gift_card_amount' => $giftCardAmount,
            ]);

            if ($giftCard && $giftCardAmount > 0) {
                $giftCard->redeem($giftCardAmount);
            }

            foreach ($request->items as $item) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'subtotal' => $item['price'] * $item['quantity'],
                ]);

                $qtyToDeduct = $item['quantity'];
                $stocks = Stock::where('product_id', $item['product_id'])
                    ->where('quantity', '>', 0)
                    ->orderBy('quantity', 'desc')
                    ->get();

                foreach ($stocks as $stock) {
                    if ($qtyToDeduct <= 0) break;
                    $deduct = min($stock->quantity, $qtyToDeduct);
                    $stock->quantity -= $deduct;
                    $stock->save();
                    $qtyToDeduct -= $deduct;
                }
            }

            if ($member) {
                $pointsEarned = (int) floor($finalAmount / 1000);
                if ($pointsRedeemed > 0) {
                    $member->deductPoints($pointsRedeemed);
                }
                $member->addPoints($pointsEarned);
                $member->addSpent($grandTotal);
                $sale->update(['points_earned' => $pointsEarned]);
            }

            app(ActivityLogger::class)->log('pos_sale', $sale, "Melakukan penjualan {$sale->code}");

            DB::commit();

            return response()->json([
                'success' => true,
                'sale' => $sale->load('items.product', 'member'),
                'message' => 'Transaksi berhasil.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses transaksi: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getMembers(Request $request)
    {
        $request->validate([
            'query' => 'required|string',
        ]);

        $search = $request->input('query');

        $members = Member::where('is_active', true)
            ->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            })
            ->limit(20)
            ->get()
            ->map(function ($m) {
                return [
                    'id' => $m->id,
                    'code' => $m->code,
                    'name' => $m->name,
                    'phone' => $m->phone,
                    'membership_level' => $m->membership_level,
                    'level_label' => $m->getLevelLabel(),
                    'discount_percent' => $m->getDiscountPercent(),
                    'points' => (int) $m->points,
                    'points_rupiah' => $m->getPointsRupiahValue(),
                ];
            });

        return response()->json($members);
    }

    public function validateGiftCard(Request $request)
    {
        $request->validate([
            'code' => 'required|string|exists:gift_cards,code',
        ]);

        $giftCard = GiftCard::active()->valid()->where('code', $request->code)->first();

        if (!$giftCard) {
            return response()->json([
                'valid' => false,
                'message' => 'Gift card tidak valid, sudah habis masa berlaku, atau saldo habis',
            ]);
        }

        return response()->json([
            'valid' => true,
            'gift_card' => [
                'id' => $giftCard->id,
                'code' => $giftCard->code,
                'current_balance' => (float) $giftCard->current_balance,
            ],
        ]);
    }

    private function generateSaleCode(): string
    {
        $date = Carbon::today()->format('Ymd');
        $lastToday = Sale::whereDate('created_at', Carbon::today())
            ->orderBy('id', 'desc')
            ->first();

        $number = 1;

        if ($lastToday && $lastToday->code) {
            $lastNumber = (int) substr($lastToday->code, -3);
            $number = $lastNumber + 1;
        }

        return 'TRX-' . $date . '-' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }
}
