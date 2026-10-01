<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Content;
use Illuminate\View\View;

class ContentController extends Controller
{
    public function show(Content $content): View
    {
        $content->load('blocks');

        return view('storefront.content.show', ['content' => $content]);
    }
}
