<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Snap;
use App\Models\Order;
use App\Models\Transaction;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function getSnapToken()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');

        // Data transaksi
        $params = [
            'transaction_details' => [
                'order_id' => uniqid(),
                'gross_amount' => 100000, // Sesuaikan dengan harga
            ],
            'customer_details' => [
                'first_name' => 'Nama Depan',
                'email' => 'email@example.com',
                'phone' => '08123456789',
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
            return response()->json(['snap_token' => $snapToken]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function processPayment(Request $request)
    {
        // Konfigurasi Midtrans
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;

        // Ambil order terakhir dari session
        // $order = Order::find(Session::get('order_id'));

        // if (!$order) {
        //     return response()->json(['error' => 'Order tidak ditemukan!'], 404);
        // }

        // $transaction_details = [
        //     'order_id' => $order->id,
        //     'gross_amount' => $order->total,
        // ];

        // $customer_details = [
        //     'first_name' => Auth::user()->name,
        //     'email' => Auth::user()->email,
        //     'phone' => Auth::user()->phone,
        // ];

        $params = [
            'transaction_details' => [
                'order_id' => uniqid(),
                'gross_amount' => 100000, // Sesuaikan dengan harga
            ],
            'customer_details' => [
                'first_name' => 'Nama Depan',
                'email' => 'email@example.com',
                'phone' => '08123456789',
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
            return $snapToken;
            //return response()->json(['snap_token' => $snapToken]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function paymentNotification(Request $request)
    {
        $serverKey = config('midtrans.server_key');
        $hashed = hash('sha512', $request->order_id . $request->status_code . $request->gross_amount . $serverKey);

        if ($hashed != $request->signature_key) {
            return response()->json(['error' => 'Invalid signature key'], 403);
        }

        $order = Order::find($request->order_id);
        if (!$order) {
            return response()->json(['error' => 'Order tidak ditemukan'], 404);
        }

        if ($request->transaction_status == 'capture' || $request->transaction_status == 'settlement') {
            $order->update(['status' => 'paid']);
            Transaction::create([
                'user_id' => $order->user_id,
                'order_id' => $order->id,
                'mode' => 'online',
                'status' => 'paid',
            ]);
        } elseif ($request->transaction_status == 'cancel' || $request->transaction_status == 'expire') {
            $order->update(['status' => 'failed']);
        }

        return response()->json(['message' => 'Notification processed']);
    }
}
