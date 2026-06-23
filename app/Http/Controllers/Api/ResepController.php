<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Resep;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ResepController extends Controller
{
    // GET /api/resep
    public function index(Request $request)
    {
        $query = Resep::with(['gambars', 'bahans', 'langkahs', 'ulasans.user']);

        if ($request->filled('kategori') && $request->kategori !== 'all') {
            $query->where('kategori', $request->kategori);
        }
        if ($request->filled('search')) {
            $query->where('nama', 'like', '%' . $request->search . '%');
        }

        return response()->json([
            'status' => 'success',
            'data'   => $query->latest()->get()->map(fn($r) => $this->formatResep($r)),
        ]);
    }

    // GET /api/resep/{resep}
    public function show(Resep $resep)
    {
        $resep->load(['gambars', 'bahans', 'langkahs', 'ulasans.user']);
        return response()->json(['status' => 'success', 'data' => $this->formatResep($resep)]);
    }

    // POST /api/resep
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

        $resep = Resep::create(
            $request->only(['nama','pembuat','waktu','kesulitan','kategori','video_url'])
            + ['user_id' => $request->user()->id]
        );

        if ($request->hasFile('gambars')) {
            foreach ($request->file('gambars') as $i => $file) {
                $path = $file->store('resep', 'public');
                $resep->gambars()->create(['path' => $path, 'urutan' => $i]);
            }
        }

        foreach ($request->bahan   as $i => $b) $resep->bahans()->create(['isi' => $b, 'urutan' => $i]);
        foreach ($request->langkah as $i => $l) $resep->langkahs()->create(['isi' => $l, 'urutan' => $i]);

        $resep->load(['gambars', 'bahans', 'langkahs', 'ulasans.user']);
        return response()->json(['status' => 'success', 'data' => $this->formatResep($resep)], 201);
    }

    // GET /api/resep-saya
    public function myResep(Request $request)
    {
        $reseps = Resep::with(['gambars', 'bahans', 'langkahs', 'ulasans.user'])
            ->where('user_id', $request->user()->id)
            ->latest()->get();

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
            'nama'      => 'required|string|max:255',
            'pembuat'   => 'required|string|max:255',
            'waktu'     => 'required|string|max:100',
            'kesulitan' => 'required|in:Mudah,Menengah,Sulit',
            'kategori'  => 'required|in:Sarapan,Makan Siang,Makan Malam,Cemilan',
            'video_url' => 'nullable|url',
            'bahan'     => 'required|array|min:1',
            'bahan.*'   => 'required|string',
            'langkah'   => 'required|array|min:1',
            'langkah.*' => 'required|string',
        ]);

        $resep->update($request->only(['nama','pembuat','waktu','kesulitan','kategori','video_url']));

        $resep->bahans()->delete();
        foreach ($request->bahan as $i => $b) $resep->bahans()->create(['isi' => $b, 'urutan' => $i]);

        $resep->langkahs()->delete();
        foreach ($request->langkah as $i => $l) $resep->langkahs()->create(['isi' => $l, 'urutan' => $i]);

        $resep->load(['gambars', 'bahans', 'langkahs', 'ulasans.user']);
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

        $resep->delete();
        return response()->json(['status' => 'success', 'message' => 'Resep dihapus.']);
    }

    // ── Helper ────────────────────────────────────────────────────────────────
    private function formatResep(Resep $r): array
    {
        return [
            'id'             => $r->id,
            'user_id'        => $r->user_id,
            'nama'           => $r->nama,
            'pembuat'        => $r->pembuat,
            'waktu'          => $r->waktu,
            'kesulitan'      => $r->kesulitan,
            'kategori'       => $r->kategori,
            'video_url'      => $r->video_url,
            'average_rating' => $r->average_rating,
            'rating_count'   => $r->ulasans->count(),
            'gambars'        => $r->gambars->map(fn($g) => ['id' => $g->id, 'url' => $g->url]),
            'bahan'          => $r->bahans->pluck('isi'),
            'langkah'        => $r->langkahs->pluck('isi'),
            // ulasans menggantikan komentars
            'ulasans'        => $r->ulasans->map(fn($u) => [
                'id'         => $u->id,
                'user_id'    => $u->user_id,
                'nama'       => $u->user?->name ?? 'Anonim',
                'avatar_url' => $u->user?->avatar_url,
                'nilai'      => $u->nilai,
                'isi'        => $u->isi,
                'created_at' => $u->created_at?->toIso8601String(),
            ]),
        ];
    }
}