<?php

namespace App\Http\Controllers;

use App\Enums\InquiryCategory;
use App\Services\PublicStaticPageReadService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicStaticPageController extends Controller
{
    public function __construct(private readonly PublicStaticPageReadService $pages) {}

    public function home(): Response
    {
        return Inertia::render($this->pages->component('home'), $this->pages->props('home'));
    }

    public function about(): Response
    {
        return Inertia::render($this->pages->component('about'), $this->pages->props('about'));
    }

    public function contact(Request $request): Response
    {
        return Inertia::render($this->pages->component('contact'), [
            ...$this->pages->contactProps($request),
            'inquiryCategories' => InquiryCategory::publicOptions(),
        ]);
    }

    public function privacy(): Response
    {
        return Inertia::render($this->pages->component('privacy'), $this->pages->props('privacy'));
    }

    public function accessibility(): Response
    {
        return Inertia::render($this->pages->component('accessibility'), $this->pages->props('accessibility'));
    }

    public function disclaimer(): Response
    {
        return Inertia::render($this->pages->component('disclaimer'), $this->pages->props('disclaimer'));
    }
}
