<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Product;
use App\Models\Bid;

class AuctionWonMail extends Mailable
{
    use Queueable, SerializesModels;

    public $product;
    public $bid;
    public $reservedUntil;

    public function __construct(Product $product, Bid $bid, $reservedUntil)
    {
        $this->product = $product;
        $this->bid = $bid;
        $this->reservedUntil = $reservedUntil;
    }

    public function build()
    {
        return $this->subject('Selamat, Anda memenangkan lelang!')
            ->markdown('emails.auction_won')
            ->with([
                'product' => $this->product,
                'bid' => $this->bid,
                'reservedUntil' => $this->reservedUntil,
            ]);
    }
}
