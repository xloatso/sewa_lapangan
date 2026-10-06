<?php

namespace App\Http\Controllers;

use App\Models\Court;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CourtController extends Controller
{
    public function index()
    {
        return view('admin.courts.index', ['courts' => Court::withCount('details')->latest()->paginate(12)]);
    }

    public function create()
    {
        return view('admin.courts.form', ['court' => new Court]);
    }

    public function edit(Court $court)
    {
        return view('admin.courts.form', compact('court'));
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['Futsal', 'Badminton', 'Basket'])],
            'price_per_hour' => ['required', 'integer', 'min:1000', 'max:10000000'],
            'description' => ['required', 'string', 'max:3000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('courts', 'public');
        }
        Court::create($data);

        return redirect()->route('courts.index')->with('success', 'Lapangan ditambahkan.');
    }

    public function update(Request $request, Court $court)
    {
        $data = $this->validated($request);
        unset($data['image']);
        $oldImage = $court->image;
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('courts', 'public');
        }
        $court->update($data);
        if (isset($data['image']) && $oldImage) {
            Storage::disk('public')->delete($oldImage);
        }

        return redirect()->route('courts.index')->with('success', 'Lapangan diperbarui.');
    }

    public function destroy(Court $court)
    {
        if ($court->details()->exists()) {
            return back()->withErrors(['court' => 'Lapangan memiliki riwayat reservasi sehingga tidak dapat dihapus.']);
        }
        $image = $court->image;
        $court->delete();
        if ($image) {
            Storage::disk('public')->delete($image);
        }

        return back()->with('success', 'Lapangan dihapus.');
    }
}
