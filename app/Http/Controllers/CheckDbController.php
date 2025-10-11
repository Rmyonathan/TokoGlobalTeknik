<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class CheckDbController extends Controller
{
    public function index()
    {
        try {
            // Coba ambil semua user
            $users = User::select('id', 'name', 'email', 'role', 'created_at')->get();

            // Coba hitung jumlah tabel lain juga (opsional)
            $tables = [];
            try {
                $kasCount = DB::table('kas')->count();
                $tables['kas_count'] = $kasCount;
            } catch (\Exception $e) {
                $tables['kas_count'] = 'tabel kas tidak ditemukan';
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Database terkoneksi dengan baik!',
                'total_users' => $users->count(),
                'tables' => $tables,
                'data' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
