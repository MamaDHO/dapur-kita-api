<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Resep;
use App\Models\Ulasan;
use Illuminate\Http\Request;

class UlasanController extends Controller
{
    // POST /api/resep/{resep}/ulasan
    public function store(Request $request, Resep $resep)
    {
        $request->validate([
            'nilai' => 'required|integer|min:1|max:5',
            'isi'   => 'required|string|max:1000',
        ]);

        $user = $request->user();

        // updateOrCreate: kalau user sudah pernah ulasan resep ini → update,
        // kalau belum → buat baru. Jadi tidak error karena unique constraint.
        $ulasan = Ulasan::updateOrCreate(
            ['resep_id' => $resep->id, 'user_id' => $user->id],
            ['nilai' => $request->nilai, 'isi' => $request->isi],
        );

        // Muat relasi user supaya nama bisa langsung dikembalikan ke Flutter
        $ulasan->load('user');

        return response()->json([
            'status' => 'success',
            'data'   => $this->formatUlasan($ulasan),
        ], 201);
    }

    // GET /api/resep/{resep}/ulasan
    // (opsional — ResepController@show sudah menyertakan ulasan,
    //  tapi endpoint ini berguna kalau mau lazy-load atau refresh ulasan saja)
    public function index(Resep $resep)
    {
        $ulasans = $resep->ulasans()->with('user')->get();

        return response()->json([
            'status' => 'success',
            'data'   => $ulasans->map(fn($u) => $this->formatUlasan($u)),
        ]);
    }

    private function formatUlasan(Ulasan $u): array
    {
        return [
            'id'         => $u->id,
            'user_id'    => $u->user_id,
            'nama'       => $u->user?->name ?? 'Anonim',
            'avatar_url' => $u->user?->avatar_url,
            'nilai'      => $u->nilai,
            'isi'        => $u->isi,
            'created_at' => $u->created_at?->toIso8601String(),
        ];
    }
}