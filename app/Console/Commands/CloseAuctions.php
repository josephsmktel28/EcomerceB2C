<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\AuctionWinner;
use App\Mail\AuctionWonMail;
use App\Services\SmsService;
use Carbon\Carbon;
use Surfsidemedia\Shoppingcart\Facades\Cart;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class CloseAuctions extends Command
{
    protected $signature = 'auctions:close';
    protected $description = 'Finalize ended auctions and record winners';

    public function handle()
    {
        $now = Carbon::now();

        $products = Product::where('auction_enabled', 1)
            ->whereNotNull('auction_end')
            ->where('auction_end', '<=', $now)
            ->get();

        foreach ($products as $product) {
            // Skip if winner already recorded
            if (AuctionWinner::where('product_id', $product->id)->exists()) {
                continue;
            }

            $highestBid = $product->bids()->orderByDesc('bid_amount')->first();
            if (!$highestBid) {
                // No bids — skip or optionally notify admin
                continue;
            }

            $reservedUntil = Carbon::now()->addHours(48);

            $winner = AuctionWinner::create([
                'product_id' => $product->id,
                'user_id' => $highestBid->user_id,
                'bid_id' => $highestBid->id,
                'reserved_until' => $reservedUntil,
            ]);

            $this->info("Recorded winner for product {$product->id}: user {$highestBid->user_id}");

            $user = $highestBid->user;
            if ($user) {
                try {
                    Mail::to($user->email)->send(new AuctionWonMail($product, $highestBid, $reservedUntil));
                    $this->info("Sent auction won email to user {$user->id}");
                } catch (\Exception $e) {
                    $this->error('Failed to send auction won email: ' . $e->getMessage());
                }

                if ($user->mobile) {
                    try {
                        $message = "Selamat! Anda memenangkan lelang produk {$product->name} dengan harga Rp " . number_format($highestBid->bid_amount, 0, ',', '.') . ". Barang telah ditambahkan ke keranjang Anda.";
                        if (SmsService::send($user->mobile, $message)) {
                            $this->info("Sent auction won SMS to user {$user->id}");
                        } else {
                            $this->error("SMS not sent for user {$user->id}; Twilio config may be missing or invalid.");
                        }
                    } catch (\Exception $e) {
                        $this->error('Failed to send auction won SMS: ' . $e->getMessage());
                    }
                }
            }

            // Try to add the won product to the user's persistent cart (stored in DB)
            try {
                if (Schema::hasTable(config('cart.database.table', 'shoppingcart'))) {
                    $identifier = 'user_' . $highestBid->user_id;

                    // Restore existing stored cart for user (if any)
                    Cart::instance('cart')->restore($identifier);

                    // Add product as 1 quantity at winning bid price
                    Cart::instance('cart')->add($product->id, $product->name, 1, $highestBid->bid_amount)->associate('App\\Models\\Product');

                    // Store back to persistent storage for this user
                    Cart::instance('cart')->store($identifier);

                    $this->info("Added product {$product->id} to persistent cart for user {$highestBid->user_id}");
                }
            } catch (\Exception $e) {
                $this->error('Failed to add to persistent cart: ' . $e->getMessage());
            }
        }

        return 0;
    }
}
