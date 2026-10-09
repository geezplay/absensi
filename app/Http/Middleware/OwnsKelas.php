<?php

namespace App\Http\Middleware;

use App\Models\Kelas;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OwnsKelas
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->isAdmin()) {
            $param = $request->route('kelas');

            if ($param) {
                $kelas = $param instanceof Kelas ? $param : Kelas::find((int) $param);
                if (! $kelas) {
                    abort(404);
                }
                if ($kelas->owner_guru_id !== $user->id) {
                    abort(403, 'Akses ditolak: kelas ini bukan milik Anda.');
                }
                $request->attributes->set('kelasModel', $kelas);
            }
        }

        return $next($request);
    }
}
