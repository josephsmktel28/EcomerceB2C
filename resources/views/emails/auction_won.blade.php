@component('mail::message')
# Selamat, Anda memenangkan lelang!

Produk yang Anda menangkan:

- Nama: **{{ $product->name }}**
- Harga menang: **Rp {{ number_format($bid->bid_amount, 0, ',', '.') }}**
- Waktu reservasi barang: **{{ $reservedUntil->format('d M Y H:i') }}**

Barang ini telah dimasukkan ke keranjang Anda secara otomatis. Silakan lanjutkan ke halaman keranjang untuk menyelesaikan pembelian.

@component('mail::button', ['url' => url('/cart')])
Lihat Keranjang
@endcomponent

Terima kasih telah mengikuti lelang di toko kami.

Salam,
Tim HobiKecil64
@endcomponent
