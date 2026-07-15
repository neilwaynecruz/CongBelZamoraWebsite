<?php

namespace App\Http\Controllers;

use App\Services\PublicProgramReadService;
use Inertia\Inertia;
use Inertia\Response;

class PublicProgramController extends Controller
{
    public function __construct(
        private readonly PublicProgramReadService $programs,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Public/Programs/Index', $this->programs->indexProps());
    }

    public function gabay(): Response
    {
        return Inertia::render('Public/Programs/Gabay', $this->programs->gabayProps());
    }

    public function tulongDunong(): Response
    {
        return Inertia::render('Public/Programs/TulongDunong', $this->programs->tulongDunongProps());
    }
}
