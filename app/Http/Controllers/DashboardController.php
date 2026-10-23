<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $isAdmin = $user->role === 'admin';

        /*
         * Administrator:
         * Mengakses statistik seluruh artikel.
         *
         * Penulis:
         * Hanya mengakses statistik artikel sendiri.
         */
        $articles = Article::query()
            ->when(
                ! $isAdmin,
                fn($query) => $query->where(
                    'user_id',
                    $user->id
                )
            );

        /*
         * Statistik dashboard.
         */
        $stats = [
            // Jumlah seluruh artikel atau artikel sendiri
            'total_articles' => (clone $articles)->count(),

            // Artikel yang sudah diterbitkan
            'published_articles' => (clone $articles)
                ->where('status', 'published')
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->count(),

            // Artikel yang masih draft
            'draft_articles' => (clone $articles)
                ->where('status', 'draft')
                ->count(),

            // Jumlah pengguna aktif hanya untuk admin
            'total_users' => $isAdmin
                ? User::query()
                ->where('is_active', true)
                ->count()
                : 0,

            // Total tayangan artikel sesuai hak akses
            'total_views' => (int) (clone $articles)
                ->sum('views'),
        ];

        return Inertia::render('admin/Dashboard', [
            'stats' => $stats,
        ]);
    }
}
