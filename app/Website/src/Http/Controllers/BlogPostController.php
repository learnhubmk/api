<?php

namespace App\Website\Http\Controllers;

use App\Framework\Enums\BlogPostStatus;
use App\Framework\Http\Controllers\Controller;
use App\Website\Http\Resources\BlogPostResource;
use App\Website\Http\Resources\SingleBlogPostResource;
use App\Website\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;

class BlogPostController extends Controller
{
    #[Endpoint(title: 'Blog posts', description: 'This endpoint list all blog post from newest ones to the oldest.Additionally you may use ?title= query param to filter by title')]
    #[Group('Website')]
    #[QueryParam('title', 'string', required: false)]
    #[QueryParam('per_page', 'integer', required: false)]
    public function index(Request $request): AnonymousResourceCollection
    {
        $blogs = BlogPost::with('author', 'tags')
            ->where('status', BlogPostStatus::PUBLISHED)
            ->when($request->has('title'), function ($query) use ($request) {
                return $query->where('title', 'like', "%{$request->title}%");
            })
            ->orderBy('publish_date', 'desc')
            ->paginate(min((int) $request->query('per_page') ?? 20, 100));

        return BlogPostResource::collection($blogs);
    }

    #[Endpoint(title: 'Blog Post', description: 'This endpoint retrieves blog post by a slug.')]
    #[Group('Website')]
    public function show(string $slug): SingleBlogPostResource
    {
        $blog = BlogPost::with('author', 'tags')
            ->where('status', BlogPostStatus::PUBLISHED)
            ->where('slug', $slug)
            ->firstOrFail();

        return new SingleBlogPostResource($blog);
    }
}
