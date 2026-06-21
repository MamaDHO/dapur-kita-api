<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Resep;
use App\Models\Rating;
use App\Models\Komentar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ResepController extends Controller
{
    // GET /api/resep?kategori=Sarapan&search=nasi
    public function index(Request $request)
    {
        $query = Resep::with(['gambars', 'bahans', 'langkahs', 'ratings', 'komentars']);

        if ($request->filled('kategori') && $request->kategori !== 'all') {
            $query->where('kategori', $request->kategori);
        }
        if ($request->filled('search')) {
            $query->where('nama', 'like', '%' . $request->search . '%');
        }

        $reseps = $query->latest()->get();

        return response()->json([
            'status'  => 'success',
            'data'    => $reseps->map(fn($r) => $this->formatResep($r)),
        ]);
    }

    // GET /api/resep/{id}
    public function show(Resep $resep)
    {
        $resep->load(['gambars', 'bahans', 'langkahs', 'ratings', 'komentars']);
        return response()->json(['status' => 'success', 'data' => $this->formatResep($resep)]);
    }

    // POST /api/resep  (multipart/form-data)
    public function store(Request $request)
    {
        $request->validate([
            'nama'       => 'required|string|max:255',
            'pembuat'    => 'required|string|max:255',
            'waktu'      => 'required|string|max:100',
            'kesulitan'  => 'required|in:Mudah,Menengah,Sulit',
            'kategori'   => 'required|in:Sarapan,Makan Siang,Makan Malam,Cemilan',
            'video_url'  => 'nullable|url',
            'gambars'    => 'nullable|array|max:3',
            'gambars.*'  => 'image|mimes:jpg,jpeg,png,webp|max:3072',
            'bahan'      => 'required|array|min:1',
            'bahan.*'    => 'required|string',
            'langkah'    => 'required|array|min:1',
            'langkah.*'  => 'required|string',
        ]);

        $resep = Resep::create($request->only(['nama','pembuat','waktu','kesulitan','kategori','video_url']) + [
            'user_id' => $request->user()->id,   // ← otomatis dari token login
        ]);

        if ($request->hasFile('gambars')) {
            foreach ($request->file('gambars') as $i => $file) {
                $path = $file->store('resep', 'public');
                $resep->gambars()->create(['path' => $path, 'urutan' => $i]);
            }
        }

        foreach ($request->bahan as $i => $b)   $resep->bahans()->create(['isi' => $b, 'urutan' => $i]);
        foreach ($request->langkah as $i => $l) $resep->langkahs()->create(['isi' => $l, 'urutan' => $i]);

        $resep->load(['gambars', 'bahans', 'langkahs', 'ratings', 'komentars']);
        return response()->json(['status' => 'success', 'data' => $this->formatResep($resep)], 201);
    }

    // GET /api/resep-saya
    public function myResep(Request $request)
    {
        $reseps = Resep::with(['gambars', 'bahans', 'langkahs', 'ratings', 'komentars'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $reseps->map(fn($r) => $this->formatResep($r)),
        ]);
    }

    // PUT /api/resep/{resep}
    public function update(Request $request, Resep $resep)
    {
        if ($resep->user_id !== $request->user()->id) {
            return response()->json(['status' => 'error', 'message' => 'Tidak diizinkan.'], 403);
        }

        $request->validate([
            'nama'       => 'required|string|max:255',
            'pembuat'    => 'required|string|max:255',
            'waktu'      => 'required|string|max:100',
            'kesulitan'  => 'required|in:Mudah,Menengah,Sulit',
            'kategori'   => 'required|in:Sarapan,Makan Siang,Makan Malam,Cemilan',
            'video_url'  => 'nullable|url',
            'bahan'      => 'required|array|min:1',
            'bahan.*'    => 'required|string',
            'langkah'    => 'required|array|min:1',
            'langkah.*'  => 'required|string',
        ]);

        $resep->update($request->only(['nama','pembuat','waktu','kesulitan','kategori','video_url']));

        $resep->bahans()->delete();
        foreach ($request->bahan as $i => $b) $resep->bahans()->create(['isi' => $b, 'urutan' => $i]);

        $resep->langkahs()->delete();
        foreach ($request->langkah as $i => $l) $resep->langkahs()->create(['isi' => $l, 'urutan' => $i]);

        $resep->load(['gambars', 'bahans', 'langkahs', 'ratings', 'komentars']);
        return response()->json(['status' => 'success', 'data' => $this->formatResep($resep)]);
    }

    // DELETE /api/resep/{resep}
    public function destroy(Request $request, Resep $resep)
    {
        if ($resep->user_id !== $request->user()->id) {
            return response()->json(['status' => 'error', 'message' => 'Tidak diizinkan.'], 403);
        }

        foreach ($resep->gambars as $gambar) {
            Storage::disk('public')->delete($gambar->path);
        }

        $resep->delete(); // cascade hapus gambars/bahans/langkahs/ratings/komentars otomatis

        return response()->json(['status' => 'success', 'message' => 'Resep dihapus.']);
    }

    // POST /api/resep/{id}/rating
    public function addRating(Request $request, Resep $resep)
    {
        $request->validate(['nilai' => 'required|integer|min:1|max:5']);
        $resep->ratings()->create(['nilai' => $request->nilai]);
        return response()->json(['status' => 'success', 'average' => $resep->average_rating]);
    }

    // POST /api/resep/{id}/komentar
    public function addKomentar(Request $request, Resep $resep)
    {
        $request->validate(['nama' => 'nullable|string|max:100', 'isi' => 'required|string']);
        $k = $resep->komentars()->create([
            'nama' => $request->nama ?? 'Anonim',
            'isi'  => $request->isi,
        ]);
        return response()->json(['status' => 'success', 'data' => $k], 201);
    }

    // ── Helper ──────────────────────────────────────────────────────────────
    private function formatResep(Resep $r): array
    {
        return [
            'id'             => $r->id,
            'nama'           => $r->nama,
            'pembuat'        => $r->pembuat,
            'waktu'          => $r->waktu,
            'kesulitan'      => $r->kesulitan,
            'kategori'       => $r->kategori,
            'video_url'      => $r->video_url,
            'average_rating' => $r->average_rating,
            'rating_count'   => $r->ratings->count(),
            'gambars'        => $r->gambars->map(fn($g) => [
                'id'  => $g->id,
                'url' => 'http://127.0.0.1:8000/storage/' . $g->path 
            ]),
            'bahan'          => $r->bahans->pluck('isi'),
            'langkah'        => $r->langkahs->pluck('isi'),
            'komentars'      => $r->komentars->map(fn($k) => [
                'id'         => $k->id,
                'nama'       => $k->nama,
                'isi'        => $k->isi,
                'created_at' => $k->created_at->toIso8601String(),
            ]),
        ];
    }
}