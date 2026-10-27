<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Kartu Stok — Sistem Manajemen Stok Laravel + Filament</title>
<meta name="description" content="Aplikasi manajemen stok untuk usaha kecil Indonesia: kartu stok, pembelian, penjualan, penyesuaian, dashboard, laporan Excel/PDF. Laravel 13 + Filament 5, jalan dengan satu perintah Docker.">
<style>
/* ponytail: self-contained Blade page, no build step; if Vite assets land later, move these styles to a css file */
@import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:ital,wght@0,400;0,500;0,600;1,400&display=swap');

:root{
  --paper:#F7F5EE;
  --paper-deep:#EFEBDD;
  --ink:#20221D;
  --ink-soft:#4A4C42;
  --rule:#CFC9B4;
  --masuk:#146B45;
  --keluar:#B3261E;
  --stempel:#1D4E89;
  --mono:'IBM Plex Mono',ui-monospace,'Cascadia Mono',Menlo,Consolas,monospace;
  --sans:system-ui,-apple-system,'Segoe UI',Roboto,Arial,sans-serif;
}

*,*::before,*::after{box-sizing:border-box}
html{scroll-behavior:smooth}
body{
  margin:0;
  background:var(--paper);
  color:var(--ink);
  font-family:var(--sans);
  font-size:16px;
  line-height:1.6;
}

/* faint ledger grid, verticals every 2rem like ledger columns */
.sheet{
  background-image:linear-gradient(90deg,var(--rule) 1px,transparent 1px);
  background-size:2rem 100%;
}
@media (min-width:64em){
  .wrap{padding-inline:2rem}
}
.wrap{max-width:70rem;margin-inline:auto;padding-inline:1.25rem}
.mono{font-family:var(--mono);font-variant-numeric:tabular-nums}

a{color:inherit}
a:focus-visible,button:focus-visible,summary:focus-visible{
  outline:2px solid var(--stempel);
  outline-offset:2px;
}

/* ============ header ============ */
header.topline{
  border-bottom:2px solid var(--ink);
  background:var(--paper);
  position:sticky;top:0;z-index:5;
}
.topline .wrap{
  display:flex;align-items:baseline;gap:1.25rem;
  padding-block:.75rem;
  flex-wrap:wrap;
}
.brandmark{
  font-family:var(--mono);
  font-weight:600;
  font-size:1.05rem;
  letter-spacing:.02em;
  text-decoration:none;
  display:flex;gap:.6rem;align-items:baseline;
}
.brandmark .dot{
  width:.55em;height:.55em;border-radius:50%;
  background:var(--masuk);
  display:inline-block;
  align-self:center;
}
.topnav{
  display:flex;gap:1.25rem;flex-wrap:wrap;
  font-family:var(--mono);font-size:.85rem;
  margin-inline-start:auto;
}
.topnav a{text-decoration:none}
.topnav a:hover{text-decoration:underline;text-underline-offset:4px}

/* ============ hero ============ */
.hero{padding-block:4.5rem 3.5rem;border-bottom:1px solid var(--ink)}
.hero-grid{display:grid;gap:3rem;grid-template-columns:1fr}
@media (min-width:64em){.hero-grid{grid-template-columns:1.05fr 1fr;gap:4rem;align-items:start}}

.hero h1{
  font-family:var(--mono);
  font-weight:600;
  font-size:clamp(1.9rem,4.4vw,3rem);
  line-height:1.18;
  margin:0 0 1rem;
  text-wrap:balance;
  letter-spacing:-.01em;
}
.hero .lede{
  font-size:1.1rem;
  color:var(--ink-soft);
  max-width:34ch;
  margin:0 0 2rem;
  text-wrap:pretty;
}
.hero-cta{display:flex;gap:1rem;flex-wrap:wrap;align-items:center}

.btn{
  font-family:var(--mono);font-size:.95rem;
  display:inline-block;
  padding:.7rem 1.25rem;
  border:2px solid var(--ink);
  text-decoration:none;
  background:var(--ink);color:var(--paper);
  transition:translate .15s ease,box-shadow .15s ease;
}
.btn:hover{translate:2px 2px;box-shadow:-2px 2px 0 var(--ink), inset 2px 2px 0 var(--paper)}
.btn.ghost{background:transparent;color:var(--ink)}
.btn.ghost:hover{box-shadow:-2px 2px 0 var(--ink)}

/* the stock card itself */
.kartu{
  border:1.5px solid var(--ink);
  background:#fff;
  box-shadow:4px 4px 0 var(--paper-deep);
}
.kartu-head{
  border-bottom:1.5px solid var(--ink);
  padding:.85rem 1rem;
  display:flex;justify-content:space-between;gap:1rem;flex-wrap:wrap;
  font-family:var(--mono);font-size:.8rem;
}
.kartu-head b{font-weight:600}
.kartu-head .gudang{color:var(--ink-soft)}
table.buku{
  width:100%;border-collapse:collapse;
  font-family:var(--mono);
  font-size:.85rem;
}
.buku caption{
  text-align:left;
  font-family:var(--mono);font-size:.8rem;
  color:var(--ink-soft);
  padding:.6rem 1rem;
  border-bottom:1px solid var(--rule);
  caption-side:top;
}
.buku th,.buku td{
  padding:.55rem 1rem;
  text-align:right;
  border-bottom:1px solid var(--rule);
  white-space:nowrap;
}
.buku th{font-weight:600;font-size:.72rem;color:var(--ink-soft)}
.buku th:first-child,.buku td:first-child{text-align:left}
.buku td:first-child{color:var(--ink-soft)}
.buku td:nth-child(2){text-align:left}
.masuk{color:var(--masuk)}
.keluar{color:var(--keluar)}
.buku tfoot td{
  border-bottom:0;
  border-top:2px solid var(--ink);
  font-weight:600;
  background:var(--paper-deep);
}
@media (prefers-reduced-motion:reduce){
  .buku tbody tr{animation:none!important;opacity:1!important;translate:none!important}
}
@keyframes masuk-buku{
  from{opacity:0;translate:0 .5rem}
  to{opacity:1;translate:0 0}
}
.buku tbody tr{
  animation:masuk-buku .45s ease-out both;
}
.buku tbody tr:nth-child(1){animation-delay:.15s}
.buku tbody tr:nth-child(2){animation-delay:.4s}
.buku tbody tr:nth-child(3){animation-delay:.65s}
.buku tbody tr:nth-child(4){animation-delay:.9s}
.buku tbody tr:nth-child(5){animation-delay:1.15s}

/* running totals under the card */
.saldo-strip{
  display:grid;
  grid-template-columns:repeat(3,1fr);
  margin-top:1.5rem;
  border:1.5px solid var(--ink);
  background:#fff;
}
.saldo-strip>div{
  padding:.9rem 1rem;
  border-inline-start:1px solid var(--rule);
}
.saldo-strip>div:first-child{border-inline-start:0}
.saldo-strip .lbl{font-family:var(--mono);font-size:.72rem;color:var(--ink-soft);display:block}
.saldo-strip .val{font-family:var(--mono);font-weight:600;font-size:1.4rem;font-variant-numeric:tabular-nums}
.saldo-strip .val.masuk{color:var(--masuk)}
.saldo-strip .val.keluar{color:var(--keluar)}

/* ============ sections ============ */
section.blok{padding-block:3.5rem;border-bottom:1px solid var(--rule)}
.blok h2{
  font-family:var(--mono);
  font-weight:600;
  font-size:clamp(1.35rem,2.6vw,1.75rem);
  margin:0 0 .4rem;
  text-wrap:balance;
}
.blok .sub{color:var(--ink-soft);margin:0 0 2.25rem;max-width:52ch;text-wrap:pretty}

/* features as ruled form sections, two columns, no card grid */
.fitur{
  display:grid;
  grid-template-columns:1fr;
  border-top:1.5px solid var(--ink);
}
@media (min-width:48em){.fitur{grid-template-columns:1fr 1fr;column-gap:3rem}}
.fitur>div{
  border-bottom:1px solid var(--rule);
  padding-block:1.5rem;
  break-inside:avoid;
}
.fitur h3{
  font-family:var(--mono);
  font-size:.95rem;
  font-weight:600;
  margin:0 0 .35rem;
}
.fitur h3 .kode{color:var(--stempel);font-weight:500;margin-inline-end:.5rem}
.fitur p{margin:0;color:var(--ink-soft);font-size:.95rem;text-wrap:pretty}

/* stack = hollow stamp box */
.stack{
  border:2.5px solid var(--stempel);
  color:var(--stempel);
  border-radius:.4rem;
  padding:2.25rem 2rem;
  max-width:34rem;
  transform:rotate(-1.2deg);
  font-family:var(--mono);
}
.stack h3{
  font-size:1rem;
  margin:0 0 1.25rem;
  font-weight:600;
  text-transform:uppercase;
  letter-spacing:.06em;
}
.stack ol{margin:0;padding-inline-start:1.2rem;font-size:.9rem;line-height:1.9}
.stack code{
  background:var(--paper-deep);
  padding:.1rem .35rem;
  font-size:.85rem;
}
.stack .catatan{
  margin-top:1.25rem;
  font-size:.78rem;
  color:var(--ink-soft);
  font-family:var(--sans);
}

/* reports: definition rows like a printed form */
.laporan{border-top:1.5px solid var(--ink)}
.laporan-row{
  display:grid;
  grid-template-columns:minmax(9rem,14rem) 1fr;
  gap:.25rem 1.5rem;
  padding-block:1rem;
  border-bottom:1px solid var(--rule);
}
.laporan-row dt{font-family:var(--mono);font-weight:600;font-size:.9rem}
.laporan-row dd{margin:0;color:var(--ink-soft);font-size:.95rem;text-wrap:pretty}
.laporan-row .bisa{
  font-family:var(--mono);font-size:.75rem;
  color:var(--stempel);
  margin-inline-start:.75rem;
}

/* CTA strip like stock-card footer */
.cta-strip{
  background:var(--paper-deep);
  padding-block:3.5rem;
  border-bottom:2px solid var(--ink);
}
.cta-inner{
  display:flex;gap:2rem;flex-wrap:wrap;align-items:center;justify-content:space-between;
}
.cta-inner h2{
  font-family:var(--mono);font-weight:600;
  font-size:clamp(1.25rem,2.4vw,1.6rem);
  margin:0;
  text-wrap:balance;
}
.cta-inner p{margin:.35rem 0 0;color:var(--ink-soft);font-size:.95rem}
.cta-cmd{
  font-family:var(--mono);font-size:.85rem;
  background:#fff;border:1.5px solid var(--ink);
  padding:.55rem .9rem;
  display:inline-flex;gap:.75rem;align-items:center;
  margin-top:1rem;
}
.cta-cmd .prompt{color:var(--stempel)}

footer.kaki{
  padding-block:2rem 2.5rem;
  font-family:var(--mono);font-size:.78rem;color:var(--ink-soft);
}
.kaki .wrap{display:flex;gap:1.5rem;flex-wrap:wrap;justify-content:space-between}
.kaki a{color:inherit}

@media print{
  .topline,.hero-cta,.cta-strip{display:none}
  body{background:#fff}
  .kartu{box-shadow:none}
}
</style>
</head>
<body>
<div class="sheet">
<a href="#konten" class="skip" style="position:absolute;left:-9999px">Langsung ke konten</a>

<header class="topline">
  <div class="wrap">
    <a class="brandmark" href="/"><span class="dot" aria-hidden="true"></span>Kartu Stok</a>
    <nav class="topnav" aria-label="Navigasi utama">
      <a href="#fitur">Fitur</a>
      <a href="#teknologi">Teknologi</a>
      <a href="#laporan">Laporan</a>
      <a href="/admin">Masuk</a>
    </nav>
  </div>
</header>

<main id="konten">

  <section class="hero">
    <div class="wrap hero-grid">
      <div>
        <h1>Setiap barang masuk dan keluar tercatat di satu kartu stok.</h1>
        <p class="lede">
          Aplikasi manajemen stok untuk usaha kecil Indonesia. Catat pembelian,
          penjualan, dan penyesuaian — saldo stok dihitung dari catatan, bukan diketik manual.
        </p>
        <div class="hero-cta">
          <a class="btn" href="/admin">Masuk ke aplikasi</a>
          <a class="btn ghost" href="#fitur">Lihat fitur</a>
        </div>
      </div>

      <div>
        <figure class="kartu" style="margin:0">
          <div class="kartu-head">
            <div><b>KARTU STOK</b> GULA PASIR 1 KG</div>
            <div class="gudang">Gudang Utama · BG-001</div>
          </div>
          <table class="buku">
            <caption>Contoh catatan pergerakan stok</caption>
            <thead>
              <tr><th scope="col">Tanggal</th><th scope="col">Keterangan</th><th scope="col">Masuk</th><th scope="col">Keluar</th><th scope="col">Saldo</th></tr>
            </thead>
            <tbody>
              <tr><td>02/10</td><td>Saldo awal</td><td></td><td></td><td>120</td></tr>
              <tr><td>04/10</td><td>Pembelian PO-0841</td><td class="masuk">+200</td><td></td><td>320</td></tr>
              <tr><td>07/10</td><td>Penjualan SO-1207</td><td></td><td class="keluar">−80</td><td>240</td></tr>
              <tr><td>09/10</td><td>Penyesuaian: rusak</td><td></td><td class="keluar">−6</td><td>234</td></tr>
              <tr><td>12/10</td><td>Penjualan SO-1215</td><td></td><td class="keluar">−54</td><td>180</td></tr>
            </tbody>
            <tfoot>
              <tr><td colspan="2">Saldo akhir</td><td class="masuk">200</td><td class="keluar">140</td><td>180</td></tr>
            </tfoot>
          </table>
        </figure>
        <div class="saldo-strip" role="group" aria-label="Ringkasan bulan Oktober">
          <div><span class="lbl">stok masuk</span><span class="val masuk">200</span></div>
          <div><span class="lbl">stok keluar</span><span class="val keluar">140</span></div>
          <div><span class="lbl">saldo</span><span class="val">180</span></div>
        </div>
      </div>
    </div>
  </section>

  <section class="blok" id="fitur">
    <div class="wrap">
      <h2>Fitur</h2>
      <p class="sub">Modul-modul yang dipakai tiap hari di gudang: data utama, transaksi, dan kartu stok yang selalu cocok dengan catatan.</p>
      <div class="fitur">
        <div>
          <h3><span class="kode">A</span>Data utama</h3>
          <p>Kategori bertingkat, satuan, pemasok, pelanggan, dan beberapa gudang. Produk dengan unggah gambar dan deskripsi lengkap.</p>
        </div>
        <div>
          <h3><span class="kode">B</span>Pembelian &amp; penjualan</h3>
          <p>Baris barang berulang dengan subtotal dihitung langsung. Total dokumen dihitung ulang di server, angka dari browser tidak dipercaya begitu saja.</p>
        </div>
        <div>
          <h3><span class="kode">C</span>Kartu stok</h3>
          <p>Saldo stok adalah hasil hitung dari catatan pergerakan yang ditulis otomatis. Penulisan langsung ke tabel stok ditolak, jadi saldo tidak bisa lari dari catatan.</p>
        </div>
        <div>
          <h3><span class="kode">D</span>Penyesuaian</h3>
          <p>Tambah atau kurangi stok dengan alasan dan nama penyetor. Selalu ada jejak siapa mengubah apa dan kenapa.</p>
        </div>
        <div>
          <h3><span class="kode">E</span>Ikhtisar stok &amp; peringatan</h3>
          <p>Lihat semua stok dalam tampilan baca-saja dengan penanda stok menipis, supaya pesan ulang ke pemasok tidak telat.</p>
        </div>
        <div>
          <h3><span class="kode">F</span>Dasbor</h3>
          <p>Kartu statistik, grafik penjualan bulanan, dan lima produk terlaris — gambaran cepat sebelum buka laporan penuh.</p>
        </div>
      </div>
    </div>
  </section>

  <section class="blok" id="teknologi">
    <div class="wrap">
      <h2>Teknologi</h2>
      <p class="sub">Tumpukan yang lazim dan bisa diaudit: Laravel 13, Filament 5, MySQL. Jalankan semuanya dengan satu perintah Docker.</p>
      <div class="stack">
        <h3>Tumpukan teknologi</h3>
        <ol>
          <li>PHP 8.4+</li>
          <li>Laravel 13</li>
          <li>Filament 5 dan Livewire</li>
          <li>MySQL 8.4</li>
          <li>Laravel Sail (Docker)</li>
        </ol>
        <p class="catatan">Setelah <code>docker compose up -d</code>, aplikasi siap dipakai di <code>localhost</code>. Login admin pertama sudah disediakan seeder.</p>
      </div>
    </div>
  </section>

  <section class="blok" id="laporan">
    <div class="wrap">
      <h2>Laporan</h2>
      <p class="sub">Empat laporan, filter dihormati penuh, dan setiap laporan bisa diekspor ke dua format.</p>
      <dl class="laporan">
        <div class="laporan-row">
          <dt>Laporan Stok<span class="bisa">Excel · PDF</span></dt>
          <dd>Saldo tiap produk per gudang, termasuk penanda stok menipis.</dd>
        </div>
        <div class="laporan-row">
          <dt>Laporan Pembelian<span class="bisa">Excel · PDF</span></dt>
          <dd>Riwayat pembelian per pemasok dan rentang tanggal.</dd>
        </div>
        <div class="laporan-row">
          <dt>Laporan Penjualan<span class="bisa">Excel · PDF</span></dt>
          <dd>Penjualan per pelanggan dan periode, lengkap dengan total.</dd>
        </div>
        <div class="laporan-row">
          <dt>Laba Rugi<span class="bisa">Excel · PDF</span></dt>
          <dd>Perhitungan laba kotor dari harga beli dan harga jual.</dd>
        </div>
      </dl>
    </div>
  </section>

  <section class="cta-strip">
    <div class="wrap cta-inner">
      <div>
        <h2>Siap mencatat stok hari ini?</h2>
        <p>Satu perintah Docker, lalu buka <span class="mono">/admin</span>.</p>
        <div class="cta-cmd"><span class="prompt">$</span><span>docker compose up -d</span></div>
      </div>
      <div>
        <a class="btn" href="/admin">Masuk ke aplikasi</a>
      </div>
    </div>
  </section>

</main>

<footer class="kaki">
  <div class="wrap">
    <div>Kartu Stok, sistem manajemen stok Laravel + Filament. Lisensi MIT.</div>
    <div><a href="https://github.com/katsugtgz/inventory-management">Kode sumber di GitHub</a></div>
  </div>
</footer>
</div>
</body>
</html>
