<?php

namespace App\Http\Controllers;

use App\Services\PublicNewsReadService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicNewsController extends Controller
{
    public function __construct(
        private readonly PublicNewsReadService $news,
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Public/News/Index', $this->news->indexProps($request));
    }
}
