<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/Dashboard', [
            'stats' => [
                'total_articles' => Article::count(),

                'published_articles' => Article::query()
                    ->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now())
                    ->count(),

                'draft_articles' => Article::query()
                    ->where('status', 'draft')
                    ->count(),

                'total_users' => User::query()
                    ->where('is_active', true)
                    ->count(),

                'total_views' => Article::sum('views'),
            ],
        ]);
    }
}
