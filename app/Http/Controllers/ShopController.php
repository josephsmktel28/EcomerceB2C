<?php

namespace App\Http\Controllers;

use App\Models\Bid;
use App\Models\Product;
use App\Models\AuctionWinner;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use App\Models\Brand;
use App\Models\Category;
use Surfsidemedia\Shoppingcart\Facades\Cart;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $size = $request->query('size') ? $request->query('size') : 12;
        $o_column = 'id';
        $o_order = 'DESC';
        $order = $request->query('order') ? $request->query('order') : -1;
        $f_brands = $request->query('brands');
        $f_categories = $request->query('categories');
        $min_price = $request->query('min') ? $request->query('min') : 0;
        $max_price = $request->query('max') ? $request->query('max') : 10000000;

        switch ($order) {
            case 1:
                $o_column = 'created_at';
                $o_order = 'DESC';
                break;
            case 2:
                $o_column = 'created_at';
                $o_order = 'ASC';
                break;
            case 3:
                $o_column = 'sale_price';
                $o_order = 'ASC';
                break;
            case 4:
                $o_column = 'sale_price';
                $o_order = 'DESC';
                break;
        }

        $brands = Brand::withCount([
            'product as auction_products_count' => function ($query) {
                $query->where('auction_enabled', 1);
            },
            'product as regular_products_count' => function ($query) {
                $query->where('auction_enabled', 0);
            },
        ])->orderBy('name', 'ASC')->get();

        $categories = Category::withCount([
            'product as auction_products_count' => function ($query) {
                $query->where('auction_enabled', 1);
            },
            'product as regular_products_count' => function ($query) {
                $query->where('auction_enabled', 0);
            },
        ])->orderBy('name', 'ASC')->get();

        $products = Product::query();

        $isAuctionPage = $request->query('auction') === '1' || $request->routeIs('shop.auctions');
        if ($isAuctionPage) {
            $products->where('auction_enabled', 1)
                ->where(function($q){
                    $q->whereNull('auction_start')->orWhere('auction_start', '<=', now());
                })->where(function($q){
                    $q->whereNull('auction_end')->orWhere('auction_end', '>=', now());
                });
        } else {
            // On regular shop listing, exclude auction products
            $products->where('auction_enabled', 0);
        }

        if ($f_brands) {
            $brandIds = explode(',', $f_brands);
            $products->whereIn('brand_id', $brandIds);
        }

        if ($f_categories) {
            $categoryIds = explode(',', $f_categories);
            $products->whereIn('category_id', $categoryIds);
        }

        // Filter harga
        $products->where(function ($query) use ($min_price, $max_price) {
            $query->where(function ($subQuery) use ($min_price, $max_price) {
                $subQuery->whereNotNull('sale_price')
                    ->whereBetween('sale_price', [$min_price, $max_price]);
            })->orWhere(function ($subQuery) use ($min_price, $max_price) {
                $subQuery->whereNull('sale_price')
                    ->whereBetween('regular_price', [$min_price, $max_price]);
            });
        });

        $products = $products->orderBy($o_column, $o_order)->paginate($size);

        return view('shop', compact('products', 'size', 'order', 'brands', 'f_brands', 'categories', 'f_categories', 'min_price', 'max_price', 'isAuctionPage'));
    }

    public function product_details($product_slug)
    {
        $product = Product::where('slug', $product_slug)->first();
        if (!$product) {
            abort(404);
        }

        $highestBid = $product->bids()->orderByDesc('bid_amount')->first();
        $auctionEnabled = $product->auction_enabled ? true : false;
        $auctionActive = $product->isAuctionActive();
        $auctionClosed = $product->isAuctionClosed();
        $currentPrice = $auctionEnabled && $highestBid ? $highestBid->bid_amount : ($product->sale_price ?: $product->regular_price);
        $bidHistory = $product->bids()->with('user')->orderByDesc('bid_amount')->take(5)->get();
        $rproducts = Product::where('slug', '<>', $product_slug)->get()->take(8);

        $hasWinner = false;
        $isWinner = false;
        $winnerRecord = null;

        if (Schema::hasTable('auction_winners')) {
            $winnerRecord = AuctionWinner::where('product_id', $product->id)->first();
            $hasWinner = $winnerRecord !== null;
        }

        if ($auctionClosed && $highestBid) {
            $hasWinner = true;
        }

        if (auth()->check()) {
            $userId = auth()->id();
            if ($winnerRecord) {
                $isWinner = false;
                if ((int) $winnerRecord->user_id === $userId) {
                    if (is_null($winnerRecord->reserved_until)) {
                        $isWinner = true;
                    } else {
                        // safe check: reserved_until may be a Carbon instance or a string
                        if ($winnerRecord->reserved_until instanceof \Illuminate\Support\Carbon) {
                            $isWinner = $winnerRecord->reserved_until->gte(now());
                        } else {
                            try {
                                $dt = \Illuminate\Support\Carbon::parse($winnerRecord->reserved_until);
                                $isWinner = $dt->gte(now());
                            } catch (\Exception $e) {
                                $isWinner = false;
                            }
                        }
                    }
                }
            } elseif ($auctionClosed && $highestBid) {
                $isWinner = (int) $highestBid->user_id === $userId;
            }
        }

        return view('details', compact('product', 'rproducts', 'highestBid', 'currentPrice', 'bidHistory', 'auctionEnabled', 'auctionActive', 'auctionClosed', 'hasWinner', 'isWinner'));
    }

    public function submitBid(Request $request, $product_slug)
    {
        $request->validate([
            'bid_amount' => 'required|numeric|min:1',
        ]);

        $product = Product::where('slug', $product_slug)->first();
        if (!$product) {
            return back()->with('error', 'Produk tidak ditemukan.');
        }

        // Pastikan lelang sedang aktif
        if (! $product->isAuctionActive()) {
            return back()->with('error', 'Lelang untuk produk ini tidak aktif atau telah berakhir.');
        }

        $highestBid = $product->bids()->orderByDesc('bid_amount')->first();
        $minimumBid = $highestBid ? $highestBid->bid_amount + 1 : ($product->sale_price ?: $product->regular_price);

        if ($request->bid_amount < $minimumBid) {
            return back()->with('error', 'Bid harus lebih besar dari Rp ' . number_format($minimumBid, 0, ',', '.'));
        }

        Bid::create([
            'product_id' => $product->id,
            'user_id' => $request->user()->id,
            'bid_amount' => $request->bid_amount,
        ]);

        // Jika produk sudah ada di keranjang, update harga item ke harga bid terbaru.
        $cartItem = Cart::instance('cart')->content()->where('id', $product->id)->first();
        if ($cartItem) {
            Cart::instance('cart')->update($cartItem->rowId, ['price' => $request->bid_amount]);
        }

        return back()->with('status', 'Bid Anda berhasil dikirim.');
    }
}