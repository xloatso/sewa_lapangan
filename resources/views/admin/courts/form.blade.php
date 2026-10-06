@extends('layouts.app')
@section('title', 'Form Lapangan · MAUMAIN')
@section('content')
<a class="back-link" href="{{ route('courts.index') }}">← Kelola lapangan</a><div class="section-head"><div><h1>{{ $court->exists ? 'Edit' : 'Tambah' }} lapangan</h1><p class="muted">Informasi ini akan tampil di katalog pelanggan.</p></div></div>
<form class="panel court-form" method="POST" enctype="multipart/form-data" action="{{ $court->exists ? route('courts.update', $court) : route('courts.store') }}">@csrf @if($court->exists) @method('PUT') @endif
<label for="name">Nama lapangan</label><input id="name" name="name" value="{{ old('name', $court->name) }}" maxlength="255" required placeholder="Contoh: Futsal Arena 01">
<div class="field-grid"><div><label for="type">Jenis olahraga</label><select id="type" name="type">@foreach(['Futsal', 'Badminton', 'Basket'] as $type)<option @selected(old('type', $court->type) === $type)>{{ $type }}</option>@endforeach</select></div><div><label for="price_per_hour">Harga per jam (Rp)</label><input id="price_per_hour" name="price_per_hour" type="number" min="1000" max="10000000" step="1" required value="{{ old('price_per_hour', $court->price_per_hour) }}"></div></div>
<label for="description">Deskripsi & fasilitas</label><textarea id="description" name="description" rows="4" maxlength="3000" required>{{ old('description', $court->description) }}</textarea>
<label for="image">Foto lapangan</label>@if($court->image)<img class="image-preview" src="{{ asset('storage/'.$court->image) }}" alt="Foto lapangan saat ini">@endif<input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp"><p class="muted">JPG, PNG, atau WebP. Maksimal 2 MB. {{ $court->image ? 'Kosongkan jika foto tidak diganti.' : 'Opsional.' }}</p><div class="row-actions"><button class="button">Simpan lapangan</button><a class="button secondary" href="{{ route('courts.index') }}">Batal</a></div></form>
@endsection
