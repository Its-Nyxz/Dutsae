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

            <!-- 1. Modul Kasir Penjualan Cepat (POS) & Shortcut -->
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700/80 rounded-2xl p-6 shadow-sm space-y-4">
                <div class="flex items-center gap-3 border-b border-slate-100 dark:border-slate-700/60 pb-3">
                    <span class="text-xl">🛒</span>
                    <div>
                        <h4 class="font-extrabold text-base text-slate-900 dark:text-white">1. Modul Kasir Penjualan Cepat (POS) & Tombol Cepat Keyboard</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Transaksi penjualan kilat dengan dukungan keyboard shortcut lengkap</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 text-xs">
                    <div class="p-3 bg-amber-500/10 dark:bg-amber-500/10 border border-amber-500/30 rounded-xl">
                        <span class="font-black text-amber-700 dark:text-amber-400 font-mono text-sm block mb-1">[F2] Cari Barang</span>
                        <p class="text-slate-600 dark:text-slate-300">Fokus instan ke kotak pencarian nama, kode (misal: <code class="font-bold">B10</code>), atau scan barcode.</p>
                    </div>
                    <div class="p-3 bg-sky-500/10 dark:bg-sky-500/10 border border-sky-500/30 rounded-xl">
                        <span class="font-black text-sky-700 dark:text-sky-400 font-mono text-sm block mb-1">[F6] Hold Transaksi</span>
                        <p class="text-slate-600 dark:text-slate-300">Menahan keranjang pembeli saat pembeli mengambil barang tambahan, lalu melayani antrean berikutnya.</p>
                    </div>
                    <div class="p-3 bg-emerald-500/10 dark:bg-emerald-500/10 border border-emerald-500/30 rounded-xl">
                        <span class="font-black text-emerald-700 dark:text-emerald-400 font-mono text-sm block mb-1">[F9] Bayar Sekarang</span>
                        <p class="text-slate-600 dark:text-slate-300">Memproses transaksi kasir secara instan dari panel sidebar kasir.</p>
                    </div>
                    <div class="p-3 bg-purple-500/10 dark:bg-purple-500/10 border border-purple-500/30 rounded-xl">
                        <span class="font-black text-purple-700 dark:text-purple-400 font-mono text-sm block mb-1">[F10] Cetak Ulang</span>
                        <p class="text-slate-600 dark:text-slate-300">Membuka kembali struk transaksi terakhir secara instan tanpa perlu mencari di riwayat laporan.</p>
                    </div>
                </div>

                <div class="space-y-2 text-xs text-slate-600 dark:text-slate-300">
                    <p class="font-bold text-slate-900 dark:text-white">✨ Fitur Unggulan di Kasir POS:</p>
                    <ul class="list-disc list-inside space-y-1.5 pl-1">
                        <li><strong>Multi-Satuan Langsung:</strong> Kasir dapat mengubah satuan barang di tabel keranjang (misal dari <span class="italic">Batang</span> ke <span class="italic">Ikat</span> atau <span class="italic">Pcs</span> ke <span class="italic">Dus</span>), dan harga jual otomatis menyesuaikan.</li>
                        <li><strong>Input Ongkos Kirim Armada Toko:</strong> Di sidebar kasir, terdapat kolom <code class="bg-slate-100 dark:bg-slate-700 px-1 py-0.5 rounded font-bold">🚚 Ongkir (Armada)</code> untuk memasukkan biaya kirim pikap/truk toko yang langsung terakumulasi ke grand total dan otomatis tercetak di Faktur & Surat Jalan.</li>
                        <li><strong>Kirim Nota ke WhatsApp:</strong> Pada modal selesai bayar, terdapat tombol hijau <code class="bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 px-1 py-0.5 rounded font-bold">📱 Kirim Nota ke WhatsApp Pelanggan</code> yang otomatis membuka chat WA dengan format nota siap kirim.</li>
                        <li><strong>Tombol Cepat Pecahan Uang (Quick Cash):</strong> Tombol <code class="bg-slate-100 dark:bg-slate-700 px-1 py-0.5 rounded">⚡ Uang Pas</code>, <code class="bg-slate-100 dark:bg-slate-700 px-1 py-0.5 rounded">20rb</code>, <code class="bg-slate-100 dark:bg-slate-700 px-1 py-0.5 rounded">50rb</code>, <code class="bg-slate-100 dark:bg-slate-700 px-1 py-0.5 rounded">100rb</code> untuk input uang tunai kilat.</li>
                    </ul>
                </div>
            </div>

            <!-- 2. Manajemen Katalog Barang & Konversi Multi-Satuan -->
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700/80 rounded-2xl p-6 shadow-sm space-y-4">
                <div class="flex items-center gap-3 border-b border-slate-100 dark:border-slate-700/60 pb-3">
                    <span class="text-xl">📦</span>
                    <div>
                        <h4 class="font-extrabold text-base text-slate-900 dark:text-white">2. Manajemen Barang & Sistem Konversi Multi-Satuan (Unit Conversion)</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Panduan lengkap cara kerja satuan dasar, faktor pengali, dan stok otomatis</p>
                    </div>
                </div>

                <!-- Penjelasan Konsep Inti Konversi Satuan -->
                <div class="bg-amber-500/10 dark:bg-amber-500/10 border border-amber-500/30 rounded-2xl p-4 sm:p-5 space-y-3">
                    <div class="flex items-center gap-2 text-amber-700 dark:text-amber-300 font-black text-sm">
                        <span>💡</span>
                        <span>Arti & Cara Kerja Sistem Konversi Satuan:</span>
                    </div>
                    <p class="text-xs text-slate-700 dark:text-slate-300 leading-relaxed">
                        Sistem konversi satuan dirancang agar toko bisa <strong>menjual dan membeli barang dalam berbagai kemasan (Eceran, Dus, Ikat, Batang, Rol, dsb.)</strong> tanpa membuat data barang ganda dan tanpa takut stok gudang selisih.
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs pt-1">
                        <div class="bg-white dark:bg-slate-900/80 p-3 rounded-xl border border-slate-200 dark:border-slate-700">
                            <span class="font-black text-amber-600 dark:text-amber-400 block mb-1">1. Satuan Dasar (Base Unit)</span>
                            <p class="text-slate-600 dark:text-slate-400">Satuan fisik <strong>terkecil</strong> yang disimpan di database untuk mencatat sisa stok riil di gudang (misal: <em>Pcs, Batang, Meter, Kg</em>).</p>
                        </div>
                        <div class="bg-white dark:bg-slate-900/80 p-3 rounded-xl border border-slate-200 dark:border-slate-700">
                            <span class="font-black text-sky-600 dark:text-sky-400 block mb-1">2. Faktor Konversi (Pengali)</span>
                            <p class="text-slate-600 dark:text-slate-400">Jumlah isi satuan kemasan terhadap satuan dasar. Contoh: jika 1 Dus berisi 50 Pcs, maka Faktor Konversi = <strong>50</strong>.</p>
                        </div>
                        <div class="bg-white dark:bg-slate-900/80 p-3 rounded-xl border border-slate-200 dark:border-slate-700">
                            <span class="font-black text-emerald-600 dark:text-emerald-400 block mb-1">3. Harga Jual Khusus</span>
                            <p class="text-slate-600 dark:text-slate-400">Setiap satuan bisa diberi harga jual berbeda (misal harga grosir per Dus dibuat lebih murah daripada beli 50 Pcs eceran).</p>
                        </div>
                    </div>
                </div>

                <!-- Contoh Kasus Nyata di Toko Duta Sae -->
                <div class="space-y-2">
                    <span class="font-bold text-xs uppercase tracking-wider text-slate-700 dark:text-slate-300">📌 Contoh Kasus Nyata di Lapangan:</span>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs text-left border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden">
                            <thead class="bg-slate-100 dark:bg-slate-900 text-slate-600 dark:text-slate-400 uppercase font-bold text-[11px]">
                                <tr>
                                    <th class="p-2.5">Nama Barang</th>
                                    <th class="p-2.5">Satuan Terpilih</th>
                                    <th class="p-2.5">Faktor Konversi</th>
                                    <th class="p-2.5">Harga Jual</th>
                                    <th class="p-2.5">Dampak ke Stok Gudang</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700 bg-white dark:bg-slate-900/40 text-slate-700 dark:text-slate-300">
                                <tr>
                                    <td class="p-2.5 font-bold" rowspan="2">Paku Beton 5cm</td>
                                    <td class="p-2.5"><span class="bg-amber-500/10 text-amber-600 font-bold px-2 py-0.5 rounded">Pcs (Satuan Dasar)</span></td>
                                    <td class="p-2.5 font-mono">1 (Dasar)</td>
                                    <td class="p-2.5 font-mono">Rp 1.000 / pcs</td>
                                    <td class="p-2.5 text-red-500 font-semibold">Kurang 1 Pcs</td>
                                </tr>
                                <tr>
                                    <td class="p-2.5"><span class="bg-sky-500/10 text-sky-600 font-bold px-2 py-0.5 rounded">Dus (Kemasan)</span></td>
                                    <td class="p-2.5 font-mono font-bold text-sky-600">50 (1 Dus = 50 Pcs)</td>
                                    <td class="p-2.5 font-mono">Rp 45.000 / dus</td>
                                    <td class="p-2.5 text-red-500 font-semibold">Otomatis Kurang 50 Pcs</td>
                                </tr>
                                <tr>
                                    <td class="p-2.5 font-bold" rowspan="2">Besi Beton 10mm</td>
                                    <td class="p-2.5"><span class="bg-amber-500/10 text-amber-600 font-bold px-2 py-0.5 rounded">Batang (Satuan Dasar)</span></td>
                                    <td class="p-2.5 font-mono">1 (Dasar)</td>
                                    <td class="p-2.5 font-mono">Rp 85.000 / batang</td>
                                    <td class="p-2.5 text-red-500 font-semibold">Kurang 1 Batang</td>
                                </tr>
                                <tr>
                                    <td class="p-2.5"><span class="bg-sky-500/10 text-sky-600 font-bold px-2 py-0.5 rounded">Ikat (Kemasan)</span></td>
                                    <td class="p-2.5 font-mono font-bold text-sky-600">10 (1 Ikat = 10 Batang)</td>
                                    <td class="p-2.5 font-mono">Rp 830.000 / ikat</td>
                                    <td class="p-2.5 text-red-500 font-semibold">Otomatis Kurang 10 Batang</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Lokasi Rak Gudang -->
                <div class="p-3 bg-slate-50 dark:bg-slate-900/60 rounded-xl border border-slate-200 dark:border-slate-700/60 text-xs text-slate-600 dark:text-slate-300">
                    <span class="font-bold text-slate-900 dark:text-white block mb-1">📍 Pelacakan Lokasi Rak / Blok Fisik:</span>
                    <p class="leading-relaxed">
                        Setiap produk dapat dipetakan ke lokasi rak fisiknya di toko/gudang (misal: <span class="font-mono font-bold text-sky-600 dark:text-sky-400">RAK-A01</span>). Lokasi rak ini akan muncul langsung di layar kasir saat melayani pembeli dan tercetak di Surat Jalan PDF untuk mempermudah kuli/staf toko mengambil barang.
                    </p>
                </div>
            </div>

            <!-- 3. Penyesuaian Stok Gudang (Stock Opname) -->
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700/80 rounded-2xl p-6 shadow-sm space-y-4">
                <div class="flex items-center gap-3 border-b border-slate-100 dark:border-slate-700/60 pb-3">
                    <span class="text-xl">📝</span>
                    <div>
                        <h4 class="font-extrabold text-base text-slate-900 dark:text-white">3. Penyesuaian Stok (Stock Opname & Selisih Fisik)</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Pencocokan stok berkala saat ada barang rusak, patah, berkarat, atau selisih bongkar</p>
                    </div>
                </div>

                <div class="space-y-2 text-xs text-slate-600 dark:text-slate-300">
                    <p class="leading-relaxed">
                        Menu <strong>Stok Opname</strong> digunakan untuk mencocokkan stok sistem dengan stok riil di gudang tanpa perlu melakukan transaksi palsu:
                    </p>
                    <ul class="list-disc list-inside space-y-1.5 pl-1">
                        <li>Buka menu <strong>Stok Opname</strong> &rarr; klik tombol <code class="bg-amber-500/20 text-amber-700 dark:text-amber-300 px-1.5 py-0.5 rounded font-bold">+ Catat Opname / Selisih Stok</code>.</li>
                        <li>Pilih produk yang akan disesuaikan. Sistem otomatis menampilkan jumlah stok sistem saat ini.</li>
                        <li>Ketik <strong>Hitungan Stok Fisik Riil</strong>. Sistem akan menghitung selisih secara otomatis (<span class="text-emerald-600 font-bold">+</span> jika lebih, <span class="text-red-600 font-bold">-</span> jika kurang/rusak).</li>
                        <li>Pilih <strong>Alasan Penyesuaian</strong> (misal: <em>Barang Rusak/Patah/Berkarat, Semen Mengeras/Sobek, Selisih Hitung</em>) lalu klik Simpan. Mutasi stok langsung tercatat secara rapi di kartu stok barang.</li>
                    </ul>
                </div>
            </div>

            <!-- 4. Retur Penjualan (Sales Return) -->
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700/80 rounded-2xl p-6 shadow-sm space-y-4">
                <div class="flex items-center gap-3 border-b border-slate-100 dark:border-slate-700/60 pb-3">
                    <span class="text-xl">🔄</span>
                    <div>
                        <h4 class="font-extrabold text-base text-slate-900 dark:text-white">4. Retur Penjualan (Sales Return Material Proyek)</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Pengembalian sisa material dari pembeli, restock gudang, dan pemotongan saldo bon/kas</p>
                    </div>
                </div>

                <div class="space-y-2 text-xs text-slate-600 dark:text-slate-300">
                    <p class="leading-relaxed">
                        Jika pelanggan memiliki sisa semen, besi, atau paralon setelah proyek selesai dan ingin mengembalikan barang ke toko:
                    </p>
                    <ul class="list-disc list-inside space-y-1.5 pl-1">
                        <li>Buka menu <strong>Retur Barang</strong> &rarr; klik <code class="bg-amber-500/20 text-amber-700 dark:text-amber-300 px-1.5 py-0.5 rounded font-bold">+ Buat Retur Penjualan</code>.</li>
                        <li>Pilih <strong>Nomor Faktur Penjualan Asal</strong> (misal: <code>INV-2026-0001</code>). Sistem otomatis menampilkan barang yang dibeli beserta kuantitas maksimal yang boleh diretur.</li>
                        <li>Isi <strong>Kuantitas yang Diretur</strong> pada baris barang terkait.</li>
                        <li>Pilih <strong>Metode Pengembalian</strong>:
                            <ul class="list-circle list-inside pl-4 mt-1 space-y-1">
                                <li><strong class="text-amber-600">📑 Potong Bon Piutang:</strong> Saldo hutang pelanggan pada faktur tersebut otomatis berkurang senilai barang yang diretur.</li>
                                <li><strong class="text-emerald-600">💵 Uang Tunai:</strong> Toko mengembalikan uang tunai langsung ke pembeli.</li>
                                <li><strong class="text-sky-600">🔄 Ganti Barang Fisik:</strong> Tukar barang sejenis tanpa pengembalian uang.</li>
                            </ul>
                        </li>
                        <li>Setelah disimpan, sistem secara otomatis <strong>menambah kembali saldo stok fisik barang ke gudang</strong>.</li>
                    </ul>
                </div>
            </div>

            <!-- 5. Pembelian Barang Masuk (Restock Supplier) -->
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700/80 rounded-2xl p-6 shadow-sm space-y-4">
                <div class="flex items-center gap-3 border-b border-slate-100 dark:border-slate-700/60 pb-3">
                    <span class="text-xl">📥</span>
                    <div>
                        <h4 class="font-extrabold text-base text-slate-900 dark:text-white">5. Pembelian Stok dari Supplier</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Pencatatan nota pembelian barang masuk dan update stok otomatis</p>
                    </div>
                </div>

                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                    Melalui menu <strong>Barang Masuk</strong>, admin dapat mencatat faktur pembelian dari supplier. Admin dapat memasukkan barang yang dibeli dalam satuan grosir (misal: Karton / Dus / Ikat), dan sistem akan secara otomatis mengalikan dengan faktor konversi satuan untuk menambah saldo stok dasar di gudang secara otomatis dan menghitung riwayat Harga Pokok Penjualan (HPP).
                </p>
            </div>

            <!-- 6. Manajemen Piutang Pelanggan (Bon) & Penagihan WA -->
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700/80 rounded-2xl p-6 shadow-sm space-y-4">
                <div class="flex items-center gap-3 border-b border-slate-100 dark:border-slate-700/60 pb-3">
                    <span class="text-xl">📑</span>
                    <div>
                        <h4 class="font-extrabold text-base text-slate-900 dark:text-white">6. Buku Piutang (Bon) & Penagihan Cepat via WhatsApp</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Pengawasan tagihan kredit pelanggan, jatuh tempo, dan pencatatan cicilan</p>
                    </div>
                </div>

                <div class="space-y-2 text-xs text-slate-600 dark:text-slate-300">
                    <ul class="list-disc list-inside space-y-1.5 pl-1">
                        <li><strong>Tombol Penagihan Instan via WhatsApp:</strong> Pada tabel Buku Piutang, klik tombol <code class="bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 px-1.5 py-0.5 rounded font-bold">📱 Tagih WA</code> untuk mengirim pesan rincian sisa hutang dan pengingat jatuh tempo langsung ke nomor WhatsApp pembeli.</li>
                        <li><strong>Limit Piutang & Jatuh Tempo:</strong> Setiap pelanggan dapat diatur batas maksimal piutang (<span class="italic">Credit Limit</span>) dan tenor hari tempo (misal: 14 hari). Jika total bon melebihi limit, kasir akan menerima peringatan.</li>
                        <li><strong>Pencatatan Pelunasan:</strong> Saat pelanggan membayar cicilan atau melunasi bon, buka menu <strong>Buku Piutang</strong> &rarr; klik <strong>💳 Lunasi Bon</strong> &rarr; pilih metode bayar (Tunai/Transfer) &rarr; cetak Bukti Pembayaran Kuitansi Piutang PDF.</li>
                    </ul>
                </div>
            </div>

            <!-- 7. Laporan Keuangan, Omzet & Ekspor Excel -->
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700/80 rounded-2xl p-6 shadow-sm space-y-4">
                <div class="flex items-center gap-3 border-b border-slate-100 dark:border-slate-700/60 pb-3">
                    <span class="text-xl">📊</span>
                    <div>
                        <h4 class="font-extrabold text-base text-slate-900 dark:text-white">7. Laporan Omzet, Arus Kas & Ekspor Data</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Analitik penjualan real-time dan ekspor laporan</p>
                    </div>
                </div>

                <div class="space-y-2 text-xs text-slate-600 dark:text-slate-300">
                    <p class="leading-relaxed">
                        Dashboard & Menu Laporan memisahkan secara akurat antara <strong>Omzet Transaksi</strong> (nilai penjualan hari ini) dengan <strong>Arus Kas Masuk Nyata</strong> (Uang riil yang masuk dari penjualan cash + cicilan pelunasan piutang). Laporan Penjualan, Katalog Barang, dan Piutang dapat diekspor langsung dalam format <strong>Microsoft Excel (.xlsx)</strong> atau dicetak dalam format <strong>PDF Rekap Resmi</strong>.
                    </p>
                </div>
            </div>

            <!-- 8. Keamanan Akun & Otorisasi Role -->
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700/80 rounded-2xl p-6 shadow-sm space-y-4">
                <div class="flex items-center gap-3 border-b border-slate-100 dark:border-slate-700/60 pb-3">
                    <span class="text-xl">🛡️</span>
                    <div>
                        <h4 class="font-extrabold text-base text-slate-900 dark:text-white">8. Otorisasi Role Pengguna & Keamanan</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Hak akses Admin Utama vs Kasir</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
                    <div class="p-3 bg-slate-50 dark:bg-slate-900/60 rounded-xl border border-slate-200 dark:border-slate-700/60">
                        <span class="font-bold text-amber-600 dark:text-amber-400 block mb-1">👑 Role Admin (Akses Penuh):</span>
                        <p class="text-slate-600 dark:text-slate-300">Dapat mengakses Dashboard Analitik, Manajemen Pengguna Kasir, Master Barang/Satuan/Supplier, Pembelian Stok, Stok Opname, Laporan Keuangan, dan Pengaturan Sistem.</p>
                    </div>
                    <div class="p-3 bg-slate-50 dark:bg-slate-900/60 rounded-xl border border-slate-200 dark:border-slate-700/60">
                        <span class="font-bold text-sky-600 dark:text-sky-400 block mb-1">👤 Role Kasir (Operasional Kasir):</span>
                        <p class="text-slate-600 dark:text-slate-300">Difokuskan pada Kasir POS Penjualan Cepat, Retur Penjualan, Cetak Struk/Surat Jalan, dan Pencatatan Pelunasan Piutang tanpa dapat mengubah pengaturan master keuangan.</p>
                    </div>
                </div>
            </div>

        </div>
    </x-pages::settings.layout>
</section>
