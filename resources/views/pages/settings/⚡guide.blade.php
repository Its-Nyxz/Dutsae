<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('components.layouts.app')] #[Title('Panduan Penggunaan Sistem - Toko Duta Sae')] class extends Component {
    //
}; ?>

<section class="w-full">
    <x-pages::settings.layout :heading="__('Buku Panduan & Dokumentasi Sistem')" :subheading="__('Petunjuk operasional lengkap penggunaan aplikasi Toko Duta Sae')" maxWidth="max-w-4xl">
        <div class="space-y-6">

            <!-- Welcome / Overview Card -->
            <div class="bg-gradient-to-br from-amber-500/10 via-amber-500/5 to-transparent border border-amber-500/30 rounded-2xl p-6 shadow-sm">
                <div class="flex items-center gap-3 mb-2">
                    <span class="text-2xl">🏪</span>
                    <h3 class="text-lg font-black text-slate-900 dark:text-white tracking-tight">Selamat Datang di Sistem ERP & POS Toko Duta Sae</h3>
                </div>
                <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                    Sistem ini dirancang khusus untuk operasional toko besi, material, dan ritel modern yang membutuhkan transaksi kasir kilat, pelacakan lokasi rak barang, manajemen konversi multi-satuan otomatis, dan pencatatan buku piutang pelanggan secara akurat.
                </p>
            </div>

            <!-- 1. Modul Kasir Penjualan Cepat (POS) -->
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700/80 rounded-2xl p-6 shadow-sm space-y-4">
                <div class="flex items-center gap-3 border-b border-slate-100 dark:border-slate-700/60 pb-3">
                    <span class="text-xl">🛒</span>
                    <div>
                        <h4 class="font-extrabold text-base text-slate-900 dark:text-white">1. Modul Kasir Penjualan Cepat (POS)</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Transaksi penjualan kilat dengan dukungan keyboard shortcut</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs">
                    <div class="p-3 bg-amber-500/10 dark:bg-amber-500/10 border border-amber-500/30 rounded-xl">
                        <span class="font-black text-amber-700 dark:text-amber-400 font-mono text-sm block mb-1">[F2] Cari / Barcode</span>
                        <p class="text-slate-600 dark:text-slate-300">Fokus instan ke kotak pencarian. Ketik kode barang (misal: <code class="font-bold">B10</code>), nama, atau scan barcode scanner.</p>
                    </div>
                    <div class="p-3 bg-sky-500/10 dark:bg-sky-500/10 border border-sky-500/30 rounded-xl">
                        <span class="font-black text-sky-700 dark:text-sky-400 font-mono text-sm block mb-1">[F6] Hold Transaksi</span>
                        <p class="text-slate-600 dark:text-slate-300">Menahan sementara transaksi pembeli saat pembeli mengambil barang tambahan, lalu kasir dapat melayani pelanggan berikutnya.</p>
                    </div>
                    <div class="p-3 bg-emerald-500/10 dark:bg-emerald-500/10 border border-emerald-500/30 rounded-xl">
                        <span class="font-black text-emerald-700 dark:text-emerald-400 font-mono text-sm block mb-1">[F9] Bayar Sekarang</span>
                        <p class="text-slate-600 dark:text-slate-300">Memproses transaksi kasir secara instan dari panel sidebar tanpa pop-up yang memperlambat kasir.</p>
                    </div>
                </div>

                <div class="space-y-2 text-xs text-slate-600 dark:text-slate-300">
                    <p class="font-bold text-slate-900 dark:text-white">✨ Fitur Unggulan di Kasir:</p>
                    <ul class="list-disc list-inside space-y-1 pl-1">
                        <li><strong>Multi-Satuan Langsung:</strong> Kasir dapat mengubah satuan barang di tabel keranjang (misal dari <span class="italic">Batang</span> ke <span class="italic">Ikat</span> atau <span class="italic">Pcs</span> ke <span class="italic">Dus</span>), dan harga jual otomatis menyesuaikan.</li>
                        <li><strong>Tombol Cepat Pecahan Uang (Quick Cash):</strong> Tombol <code class="bg-slate-100 dark:bg-slate-700 px-1 py-0.5 rounded">⚡ Uang Pas</code>, <code class="bg-slate-100 dark:bg-slate-700 px-1 py-0.5 rounded">20rb</code>, <code class="bg-slate-100 dark:bg-slate-700 px-1 py-0.5 rounded">50rb</code>, <code class="bg-slate-100 dark:bg-slate-700 px-1 py-0.5 rounded">100rb</code> untuk input tunai kilat.</li>
                        <li><strong>Buat Barang Baru Inline:</strong> Jika ada barang yang belum terdaftar di katalog saat kasir bertugas, kasir dapat menekan tombol <code class="bg-amber-500/20 text-amber-700 dark:text-amber-300 px-1 py-0.5 rounded font-bold">➕ Buat Barang Baru</code> untuk langsung mendaftarkan produk, satuan, dan lokasi rak.</li>
                        <li><strong>Cetak Struk & Surat Jalan:</strong> Setelah pembayaran selesai, sistem otomatis menampilkan modal cetak Faktur Thermal PDF dan Surat Jalan PDF untuk pengiriman armada toko.</li>
                    </ul>
                </div>
            </div>

            <!-- 2. Manajemen Katalog Barang, Multi-Satuan & Lokasi Rak -->
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700/80 rounded-2xl p-6 shadow-sm space-y-4">
                <div class="flex items-center gap-3 border-b border-slate-100 dark:border-slate-700/60 pb-3">
                    <span class="text-xl">📦</span>
                    <div>
                        <h4 class="font-extrabold text-base text-slate-900 dark:text-white">2. Manajemen Barang, Satuan & Lokasi Rak</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Pengelolaan master data produk terstruktur</p>
                    </div>
                </div>

                <div class="space-y-3 text-xs text-slate-600 dark:text-slate-300">
                    <div class="p-3 bg-slate-50 dark:bg-slate-900/60 rounded-xl border border-slate-200 dark:border-slate-700/60">
                        <span class="font-bold text-slate-900 dark:text-white block mb-1">📐 Hierarki Multi-Satuan (Unit Conversion):</span>
                        <p class="leading-relaxed">
                            Setiap produk memiliki <strong>Satuan Dasar</strong> (faktor konversi = 1, misal: <span class="italic">Batang</span>) dan dapat ditambahkan <strong>Satuan Turunan</strong> (misal: <span class="italic">Ikat = 10 Batang</span>). Stok di database selalu dihitung dalam kuantitas dasar sehingga tidak ada selisih stok saat terjual dalam satuan eceran maupun grosir.
                        </p>
                    </div>

                    <div class="p-3 bg-slate-50 dark:bg-slate-900/60 rounded-xl border border-slate-200 dark:border-slate-700/60">
                        <span class="font-bold text-slate-900 dark:text-white block mb-1">📍 Pelacakan Lokasi Rak:</span>
                        <p class="leading-relaxed">
                            Setiap produk dapat dipetakan ke lokasi rak fisiknya di toko/gudang (misal: <span class="font-mono font-bold text-sky-600 dark:text-sky-400">RAK-A01</span>). Lokasi rak ini akan muncul di layar kasir dan tercetak di Surat Jalan untuk mempermudah kuli/staf gudang mengambil barang.
                        </p>
                    </div>
                </div>
            </div>

            <!-- 3. Pembelian Barang Masuk (Restock Supplier) -->
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700/80 rounded-2xl p-6 shadow-sm space-y-4">
                <div class="flex items-center gap-3 border-b border-slate-100 dark:border-slate-700/60 pb-3">
                    <span class="text-xl">📥</span>
                    <div>
                        <h4 class="font-extrabold text-base text-slate-900 dark:text-white">3. Pembelian Stok dari Supplier</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Pencatatan nota pembelian barang masuk dan update stok otomatis</p>
                    </div>
                </div>

                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                    Melalui menu <strong>Pembelian Stok</strong>, admin dapat mencatat faktur pembelian dari supplier. Admin dapat memasukkan barang yang dibeli dalam satuan grosir (misal: Karton / Dus), dan sistem akan secara otomatis mengalikan dengan faktor konversi satuan untuk menambah saldo stok dasar di gudang secara otomatis dan menghitung riwayat Harga Pokok Penjualan (HPP).
                </p>
            </div>

            <!-- 4. Manajemen Piutang Pelanggan (Bon) & Pelunasan -->
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700/80 rounded-2xl p-6 shadow-sm space-y-4">
                <div class="flex items-center gap-3 border-b border-slate-100 dark:border-slate-700/60 pb-3">
                    <span class="text-xl">📑</span>
                    <div>
                        <h4 class="font-extrabold text-base text-slate-900 dark:text-white">4. Buku Piutang (Bon) & Pelunasan Cicilan</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Pengawasan tagihan kredit pelanggan dan pencatatan kas masuk</p>
                    </div>
                </div>

                <div class="space-y-2 text-xs text-slate-600 dark:text-slate-300">
                    <ul class="list-disc list-inside space-y-1.5 pl-1">
                        <li><strong>Limit Piutang & Jatuh Tempo:</strong> Setiap pelanggan dapat diatur batas maksimal piutang (<span class="italic">Credit Limit</span>) dan tenor hari tempo (misal: 14 hari). Jika total bon melebihi limit, kasir akan menerima peringatan.</li>
                        <li><strong>Pencatatan Pelunasan:</strong> Saat pelanggan membayar cicilan atau melunasi bon, buka menu <strong>Piutang Pelanggan</strong> &rarr; klik <strong>Catat Pembayaran</strong> &rarr; pilih metode bayar (Tunai/Transfer) &rarr; cetak Bukti Pembayaran Kuitansi Piutang PDF.</li>
                    </ul>
                </div>
            </div>

            <!-- 5. Laporan Keuangan, Omzet & Ekspor Excel -->
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700/80 rounded-2xl p-6 shadow-sm space-y-4">
                <div class="flex items-center gap-3 border-b border-slate-100 dark:border-slate-700/60 pb-3">
                    <span class="text-xl">📊</span>
                    <div>
                        <h4 class="font-extrabold text-base text-slate-900 dark:text-white">5. Laporan Omzet, Arus Kas & Ekspor Data</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Analitik penjualan real-time dan ekspor laporan</p>
                    </div>
                </div>

                <div class="space-y-2 text-xs text-slate-600 dark:text-slate-300">
                    <p class="leading-relaxed">
                        Dashboard & Menu Laporan memisahkan secara akurat antara <strong>Omzet Transaksi</strong> (nilai penjualan hari ini) dengan <strong>Arus Kas Masuk Nyata</strong> (Uang riil yang masuk dari penjualan cash + cicilan pelunasan piutang). Laporan Penjualan, Katalog Barang, dan Piutang dapat diekspor langsung dalam format <strong>Microsoft Excel (.xlsx)</strong> atau dicetak dalam format <strong>PDF Rekap Resmi</strong>.
                    </p>
                </div>
            </div>

            <!-- 6. Keamanan Akun & Otorisasi Role -->
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700/80 rounded-2xl p-6 shadow-sm space-y-4">
                <div class="flex items-center gap-3 border-b border-slate-100 dark:border-slate-700/60 pb-3">
                    <span class="text-xl">🛡️</span>
                    <div>
                        <h4 class="font-extrabold text-base text-slate-900 dark:text-white">6. Otorisasi Role Pengguna & Keamanan</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Hak akses Admin Utama vs Kasir</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
                    <div class="p-3 bg-slate-50 dark:bg-slate-900/60 rounded-xl border border-slate-200 dark:border-slate-700/60">
                        <span class="font-bold text-amber-600 dark:text-amber-400 block mb-1">👑 Role Admin (Akses Penuh):</span>
                        <p class="text-slate-600 dark:text-slate-300">Dapat mengakses Dashboard Analitik, Manajemen Pengguna Kasir, Master Barang/Satuan/Supplier, Pembelian Stok, Laporan Keuangan, dan Pengaturan Sistem.</p>
                    </div>
                    <div class="p-3 bg-slate-50 dark:bg-slate-900/60 rounded-xl border border-slate-200 dark:border-slate-700/60">
                        <span class="font-bold text-sky-600 dark:text-sky-400 block mb-1">👤 Role Kasir (Operasional Kasir):</span>
                        <p class="text-slate-600 dark:text-slate-300">Difokuskan pada Kasir POS Penjualan Cepat, Cetak Struk, dan Pencatatan Pelunasan Piutang tanpa dapat mengubah pengaturan master keuangan.</p>
                    </div>
                </div>
            </div>

        </div>
    </x-pages::settings.layout>
</section>
