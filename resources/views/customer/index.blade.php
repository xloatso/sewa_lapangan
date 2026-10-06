@extends('layouts.app')
@section('content')
<header class="page-title"><h1>Reservasi Lapangan</h1><p>Pilih lapangan dan jadwal, lalu masukkan data pemesan Anda</p></header>
@if(session('receipt'))
    @php($receipt = session('receipt'))
    <section class="panel receipt"><span class="badge green">RESERVASI #{{ str_pad($receipt['id'], 5, '0', STR_PAD_LEFT) }}</span><h2>Reservasi berhasil diajukan, {{ $receipt['name'] }}.</h2><p>{{ $receipt['court'] }} · {{ $receipt['date'] }} · {{ $receipt['start'] }} · {{ $receipt['duration'] }} jam</p><strong class="price">Rp {{ number_format($receipt['total'], 0, ',', '.') }}</strong><p class="muted">Simpan nomor reservasi ini. Pembayaran dikonfirmasi oleh petugas.</p></section>
@endif
<div class="catalog-toolbar">
    <div class="filters" aria-label="Jenis olahraga">@foreach(['all' => 'Semua lapangan', 'Futsal' => 'Futsal', 'Badminton' => 'Badminton', 'Basket' => 'Basket'] as $value => $label)<button class="filter {{ $loop->first ? 'selected' : '' }}" type="button" data-filter="{{ $value }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}">{{ $label }}</button>@endforeach</div>
    <form method="GET" class="date-filter"><label for="date">Tanggal main</label><input id="date" name="date" type="date" value="{{ $date }}" min="{{ today()->format('Y-m-d') }}" max="{{ today()->addMonths(3)->format('Y-m-d') }}" required><button class="button small secondary">Cek jadwal</button></form>
</div>
<form id="reservation-form" action="{{ route('customer.checkout') }}" method="POST">
@csrf
<input name="booking_date" type="hidden" value="{{ $date }}">
<section class="panel customer-info">
<h2>1. Informasi Pemesan</h2>
<div class="field-grid"><div><label for="customer_name">Nama Lengkap</label><input id="customer_name" name="customer_name" value="{{ old('customer_name') }}" placeholder="Masukkan nama pemesan" maxlength="255" required autocomplete="name"></div><div><label for="customer_phone">Nomor Telepon / WhatsApp</label><input id="customer_phone" name="customer_phone" value="{{ old('customer_phone') }}" placeholder="Contoh: 081234567890" maxlength="25" required type="tel" autocomplete="tel"></div></div>
</section>
<div class="booking-layout"><div>
<h2 id="courts">2. Pilih Lapangan</h2>
    <div class="court-grid">
    @forelse($courts as $court)
        <article class="court-card" data-category="{{ $court->type }}">
            @if($court->image)<img class="court-image" src="{{ asset('storage/'.$court->image) }}" alt="{{ $court->name }}">@else<div class="court-image no-image">Tanpa Gambar</div>@endif
            <div class="card-content"><div class="card-meta"><span class="badge">{{ $court->type }}</span><span class="muted">08.00–22.00</span></div><h3>{{ $court->name }}</h3><p class="description">{{ $court->description }}</p><div class="card-bottom"><div><strong class="price">Rp {{ number_format($court->price_per_hour, 0, ',', '.') }}</strong><small class="muted"> / jam</small></div><label class="choose"><input type="radio" name="court_id" value="{{ $court->id }}" data-name="{{ $court->name }}" data-price="{{ $court->price_per_hour }}" @checked((string)old('court_id') === (string)$court->id) required> Pilih</label></div></div>
        </article>
    @empty
        <div class="panel empty">Belum ada lapangan tersedia. Silakan kembali lagi nanti.</div>
    @endforelse
    </div>
    <p id="empty-filter" class="panel empty" hidden>Belum ada lapangan untuk kategori ini.</p>
    <section class="panel schedule"><div class="panel-heading"><div><h2>3. Pilih Jadwal</h2><p class="muted">Durasi 1–4 jam. Jadwal yang sudah dipesan tidak bisa dipilih.</p></div></div>
        <div class="field-grid"><div><label for="start_time">Jam mulai</label><select id="start_time" name="start_time" required><option value="">Pilih jam</option>@foreach(range(8, 21) as $hour)@php($time = sprintf('%02d:00', $hour))<option value="{{ $time }}" @selected(old('start_time') === $time)>{{ $time }}</option>@endforeach</select></div><div><label for="duration_hours">Durasi bermain</label><select id="duration_hours" name="duration_hours">@foreach(range(1, 4) as $hour)<option value="{{ $hour }}" @selected((int) old('duration_hours', 1) === $hour)>{{ $hour }} jam</option>@endforeach</select></div></div><p id="availability-note" class="muted" aria-live="polite">Pilih lapangan untuk melihat ketersediaan jam.</p>
    </section>
</div>

</div>
<div class="checkout-area"><p>Lapangan: <strong id="selected-court">Belum ada lapangan dipilih</strong></p><p>Tanggal: {{ \Carbon\Carbon::parse($date)->locale('id')->translatedFormat('d F Y') }}</p><p>Total Pembayaran: <strong class="price" id="summary-total">Rp 0</strong></p><button id="review-booking" type="button" class="button" @disabled($courts->isEmpty())>Reservasi Sekarang</button><p class="muted">Pembayaran dikonfirmasi oleh petugas lapangan.</p><noscript><button class="button" type="submit">Kirim reservasi</button></noscript></div>
<dialog id="confirmation" aria-labelledby="confirm-title"><div class="dialog-content"><h2 id="confirm-title">Konfirmasi reservasi</h2><p class="muted">Pastikan tanggal dan jam bermain sudah sesuai.</p><div id="confirmation-details"></div><div class="dialog-actions"><button id="close-confirmation" type="button" class="button secondary">Kembali</button><button type="submit" class="button">Ya, kirim reservasi</button></div></div></dialog>
</form>
<script type="application/json" id="occupied-data">{!! json_encode($occupied, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
<script type="application/json" id="clock-data">{!! json_encode(['date' => today()->format('Y-m-d'), 'hour' => now()->hour]) !!}</script>
@endsection
