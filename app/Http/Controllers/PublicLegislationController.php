<?php

namespace App\Http\Controllers;

use App\Services\PublicLegislationReadService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicLegislationController extends Controller
{
    public function __construct(
        private readonly PublicLegislationReadService $legislation,
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Public/Legislation/Index', $this->legislation->indexProps($request));
    }
}
