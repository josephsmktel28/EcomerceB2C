<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Transaction;
use Illuminate\Support\Facades\Session;
use Surfsidemedia\Shoppingcart\Facades\Cart;
use Illuminate\Support\Facades\Auth;
use App\Models\Address;
use Midtrans\Snap;
use Midtrans\Config;
use App\Models\AuctionWinner;
use Illuminate\Support\Facades\Schema;

class CartController extends Controller
{
    // Helper function untuk mengkonversi nilai mata uang ke float
    private function currencyToFloat($value) {
        // Jika input adalah string dengan format angka
        if (is_string($value)) {
            // Hapus semua karakter non-numerik kecuali titik dan koma
            $value = preg_replace('/[^\d,.]/i', '', $value);
            // Ubah koma jadi titik jika ada
            $value = str_replace(',', '.', $value);
            // Jika ada beberapa titik (format ribuan), ambil yang terakhir sebagai desimal
            if (substr_count($value, '.') > 1) {
                $parts = explode('.', $value);
                $decimal = array_pop($parts);
                $value = implode('', $parts) . '.' . $decimal;
            }
        }
        return floatval($value);
    }

    public function index()
    {
        $items = Cart::instance('cart')->content();
        return view('cart', compact('items'));
    }

    public function add_to_cart(Request $request)
    {
        $product = \App\Models\Product::find($request->id);
        if ($product && $product->auction_enabled) {
            // If auction still active, block
            if (! $product->isAuctionClosed()) {
                return redirect()->back()->with('error', 'Produk ini merupakan item lelang dan tidak dapat ditambahkan ke keranjang sampai lelang berakhir.');
            }

            $highestBid = $product->bids()->orderByDesc('bid_amount')->first();
            $winner = null;
            if (Schema::hasTable('auction_winners')) {
                $winner = AuctionWinner::where('product_id', $product->id)->first();
            }

            $isWinner = false;
            if ($winner) {
                $isWinner = auth()->check() && auth()->id() === (int) $winner->user_id;
            } elseif ($highestBid) {
                $isWinner = auth()->check() && auth()->id() === (int) $highestBid->user_id;
            }

            if (! $highestBid || ! $isWinner) {
                return redirect()->back()->with('error', 'Lelang sudah selesai, hanya pemenang yang bisa membeli.');
            }
        }

        Cart::instance('cart')->add($request->id, $request->name, $request->quantity, $request->price)->associate('App\Models\Product');
        return redirect()->back();
    }

    public function increase_cart_quantity($rowId)
    {
        $product = Cart::instance('cart')->get($rowId);
        $qty = $product->qty + 1;
        Cart::instance('cart')->update($rowId, $qty);

        if (Session::has('coupon')) {
            $coupon = Session::get('coupon');
            if (Cart::instance('cart')->subtotal() < $coupon['cart_value']) {
                Session::forget('coupon');
                Session::forget('discounts');
                Session::forget('checkout');
                return redirect()->back()->with('error', 'Kupon telah dihapus karena total keranjang kurang dari nilai minimum.');
            }
            $this->calculateDiscount();
        }

        return redirect()->back();
    }

    public function decrease_cart_quantity($rowId)
    {
        $product = Cart::instance('cart')->get($rowId);
        $qty = $product->qty - 1;
        Cart::instance('cart')->update($rowId, $qty);

        if (Session::has('coupon')) {
            $coupon = Session::get('coupon');
            if (Cart::instance('cart')->subtotal() < $coupon['cart_value']) {
                Session::forget('coupon');
                Session::forget('discounts');
                Session::forget('checkout');
                return redirect()->back()->with('error', 'Kupon telah dihapus karena total keranjang kurang dari nilai minimum.');
            }
            $this->calculateDiscount();
        }

        return redirect()->back();
    }

    public function remove_item($rowId)
    {
        Cart::instance('cart')->remove($rowId);

        if (Session::has('coupon')) {
            $coupon = Session::get('coupon');
            if (Cart::instance('cart')->subtotal() < $coupon['cart_value']) {
                Session::forget('coupon');
                Session::forget('discounts');
                Session::forget('checkout');
                return redirect()->back()->with('error', 'Kupon telah dihapus karena total keranjang kurang dari nilai minimum.');
            }
            $this->calculateDiscount();
        }

        return redirect()->back();
    }

    public function empty_cart()
    {
        Cart::instance('cart')->destroy();
        return redirect()->back();
    }

    public function apply_coupon_code(Request $request)
    {
        $coupon_code = $request->coupon_code;
        if (isset($coupon_code)) {
            $coupon = Coupon::where('code', $coupon_code)->where('expiry_date', '>=', Carbon::today())->first();
            if (!$coupon) {
                return redirect()->back()->with('error', 'Invalid coupon code!');
            } else {
                // Periksa apakah total keranjang memenuhi syarat cart_value
                if (Cart::instance('cart')->subtotal() < $coupon->cart_value) {
                    return redirect()->back()->with('error', 'Minimum pembelian Rp' . number_format($coupon->cart_value, 0, ',', '.') . ' untuk menggunakan kupon ini.');
                }

                Session::put('coupon', [
                    'code' => $coupon->code,
                    'type' => $coupon->type,
                    'value' => $coupon->value,
                    'cart_value' => $coupon->cart_value
                ]);
                $this->calculateDiscount();
                return redirect()->back()->with('success', 'Coupon has been applied!');
            }
        } else {
            return redirect()->back()->with('error', 'Invalid coupon code!');
        }
    }

    public function calculateDiscount()
    {
        $discount = 0;
        if (Session::has('coupon')) {
            // Ambil subtotal, bersihkan dari format ribuan atau karakter lain
            $cartSubtotal = filter_var(Cart::instance('cart')->subtotal(), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $cartSubtotal = floatval($cartSubtotal); // Pastikan jadi angka float

            if (Session::get('coupon')['type'] == 'fixed') {
                $discount = floatval(Session::get('coupon')['value']);
            } else {
                $discount = ($cartSubtotal * floatval(Session::get('coupon')['value'])) / 100;
            }

            // Hitung ulang setelah diskon
            $subtotalAfterDiscount = $cartSubtotal - $discount;

            // Pastikan pajak dihitung dengan benar
            $taxRate = floatval(config('cart.tax')); // Misalnya pajak 10% = 10
            $taxAfterDiscount = ($subtotalAfterDiscount * $taxRate) / 100;

            // Total akhir setelah diskon dan pajak
            $totalAfterDiscount = $subtotalAfterDiscount + $taxAfterDiscount;

            // PERUBAHAN: Simpan nilai asli tanpa format
            Session::put('discounts', [
                'discount' => $discount,
                'subtotal' => $subtotalAfterDiscount,
                'tax' => $taxAfterDiscount,
                'total' => $totalAfterDiscount,
            ]);

            // Pastikan nilai checkout juga tersimpan agar Midtrans menggunakan total yang sudah diskon
            Session::put('checkout', [
                'discount' => $discount,
                'subtotal' => $subtotalAfterDiscount,
                'tax' => $taxAfterDiscount,
                'total' => $totalAfterDiscount,
            ]);
        }
    }

    public function remove_coupon_code()
    {
        Session::forget('coupon');
        Session::forget('discounts');
        Session::forget('checkout');
        return back()->with('success', 'Coupon has been removed');
    }

    public function checkout()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $address = Address::where('user_id', Auth::user()->id)->where('isdefault', 1)->first();

        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;

        // Dapatkan total dari session checkout jika ada.
        // Jika session checkout belum dibuat, gunakan diskon yang tersimpan.
        if (Session::has('checkout')) {
            $total = Session::get('checkout')['total'];
        } elseif (Session::has('discounts')) {
            $total = Session::get('discounts')['total'];
        } else {
            $total = Cart::instance('cart')->total();
        }

        // PERUBAHAN: Gunakan fungsi currencyToFloat untuk konversi
        $gross_amount = intval($this->currencyToFloat($total));

        $params = [
            'transaction_details' => [
                'order_id' => uniqid(),
                'gross_amount' => $gross_amount,
            ],
            'customer_details' => [
                'first_name' => $address ? $address->name : 'Tidak Ada',
                'email' => Auth::user()->email,
                'phone' => $address ? $address->phone : '-',
            ],
        ];

        $snapToken = Snap::getSnapToken($params);

        return view('checkout', compact('address', 'snapToken'));
    }

    public function place_an_order(Request $request)
    {
        $user_id = Auth::user()->id;
        $address = Address::where('user_id', $user_id)->where('isdefault', true)->first();

        if (!$address) {
            $request->validate([
                'name' => 'required|max:100',
                'phone' => 'required|numeric|digits_between:10,13',
                'zip' => 'required|numeric|digits:5',
                'state' => 'required',
                'city' => 'required',
                'address' => 'required',
                'locality' => 'required',
                'landmark' => 'required',
            ]);

            $address = new Address();
            $address->name = $request->name;
            $address->phone = $request->phone;
            $address->zip = $request->zip;
            $address->state = $request->state;
            $address->city = $request->city;
            $address->address = $request->address;
            $address->locality = $request->locality;
            $address->landmark = $request->landmark;
            $address->country = 'Indonesia';
            $address->user_id = $user_id;
            $address->isdefault = true;
            $address->save();
        }

        $this->setAmountforCheckout();

        $order = new Order();
        $order->user_id = $user_id;
        
        // PERUBAHAN: Gunakan fungsi currencyToFloat untuk konversi nilai-nilai
        $order->subtotal = round($this->currencyToFloat(Session::get('checkout')['subtotal']), 2);
        $order->discount = round($this->currencyToFloat(Session::get('checkout')['discount']), 2);
        $order->tax = round($this->currencyToFloat(Session::get('checkout')['tax']), 2);
        $order->total = round($this->currencyToFloat(Session::get('checkout')['total']), 2);
        
        $order->name = $address->name;
        $order->phone = $address->phone;
        $order->locality = $address->locality;
        $order->address = $address->address;
        $order->city = $address->city;
        $order->state = $address->state;
        $order->country = $address->country;
        $order->landmark = $address->landmark;
        $order->zip = $address->zip;
        $order->total_items = Cart::instance('cart')->count();
        $order->save();

        foreach (Cart::instance('cart')->content() as $item) {
            $orderItem = new OrderItem();
            $orderItem->product_id = $item->id;
            $orderItem->order_id = $order->id;
            $orderItem->price = $item->price;
            $orderItem->quantity = $item->qty;
            $orderItem->save();
        }

        $transaction = new Transaction();
        $transaction->user_id = $user_id;
        $transaction->order_id = $order->id;
        $transaction->mode = "paypal";
        $transaction->status = "pending";
        $transaction->save();

        Cart::instance('cart')->destroy();
        Session::forget('checkout');
        Session::forget('coupon');
        Session::forget('discounts');
        Session::put('order_id', $order->id);

        return redirect()->route('cart.order.confirmation');
    }

    public function setAmountforCheckout()
    {
        if (Cart::instance('cart')->content()->count() > 0) {
            // Cek jika ada kupon
            if (Session::has('coupon')) {
                // PERUBAHAN: Menggunakan nilai langsung dari session discounts
                Session::put('checkout', [
                    'discount' => Session::get('discounts')['discount'],
                    'subtotal' => Session::get('discounts')['subtotal'],
                    'tax' => Session::get('discounts')['tax'],
                    'total' => Session::get('discounts')['total'],
                ]);
            } else {
                // PERUBAHAN: Gunakan currencyToFloat untuk konversi
                Session::put('checkout', [
                    'discount' => 0,
                    'subtotal' => round($this->currencyToFloat(Cart::instance('cart')->subtotal()), 2),
                    'tax' => round($this->currencyToFloat(Cart::instance('cart')->tax()), 2),
                    'total' => round($this->currencyToFloat(Cart::instance('cart')->total()), 2),
                ]);
            }
        } else {
            // Kosongkan checkout jika tidak ada barang di keranjang
            Session::forget('checkout');
        }
    }

    public function order_confirmation()
    {
        if (Session::has('order_id')) {
            $order = Order::find(Session::get('order_id'));
            return view('order-confirmation', compact('order'));
        }
        return redirect()->route('cart.index');
    }
}