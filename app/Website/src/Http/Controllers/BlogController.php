<?php

namespace App\Website\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Website\Http\Resources\Blogs\ListBlogsResource;
use App\Website\Models\Blog;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\QueryParam;

class BlogController extends Controller
{
    #[Endpoint('Website/List Blogs', <<<'DESC'
  This endpoint list all blogpost from newest ones to the oldest.
  Additionally you may use ?title= query param to filter by title
 DESC)]
    #[QueryParam('title', 'string', required: false)]
    public function index(Request $request)
    {
        $blogs = Blog::with('user');

        if ($request->has('title')) {
            $blogs->where('title', 'like', "%{$request->title}%");
        }

        $blogs = $blogs->orderBy('created_at', 'desc')->paginate(15);

        return ListBlogsResource::collection($blogs);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
