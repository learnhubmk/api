<?php

namespace App\Content\Http\Controllers;

use App\Content\Http\Resources\BlogPosts\BlogPostsResource;
use App\Http\Controllers\Controller;
use App\Website\Models\BlogPost;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;

class BlogPostController extends Controller
{
    #[Endpoint(title: 'Blog posts', description: 'This endpoint list all blog post')]
    #[Group('Website')]
    #[QueryParam('title', 'string', required: false)]
    #[QueryParam('tag', 'string', required: false)]
    #[QueryParam('author', 'string', required: false)]
    public function index(Request $request)
    {
        $blogs = BlogPost::with('author', 'tags')
            ->paginate(15);

        return BlogPostsResource::collection($blogs);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    #[Endpoint(title: 'Blog post', description: 'This endpoint returns a single blog post')]
    #[Group('Website')]
    public function show(BlogPost $blog_post) : BlogPostsResource
    {
        $blog_post->load('author', 'tags');

        return new BlogPostsResource($blog_post);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    #[Endpoint(title: 'Delete Blog posts', description: 'This endpoint deletes blog post')]
    #[Group('Website')]
    public function destroy(BlogPost $blog_post)
    {
        $blog_post->delete();

        return response()->noContent();
    }
}
