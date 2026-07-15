<?php

namespace App\Http\Controllers;

use App\Services\PublicProjectReadService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicProjectController extends Controller
{
    public function __construct(
        private readonly PublicProjectReadService $projects,
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Public/Projects/Index', $this->projects->indexProps($request));
    }
}
