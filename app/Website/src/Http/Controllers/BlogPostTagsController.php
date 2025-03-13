<?php

namespace App\Website\Http\Controllers;

use App\Framework\Http\Controllers\Controller;
use App\Framework\Enums\BlogPostStatus;
use App\Website\Http\Resources\Blogs\BlogPostsResource;
use App\Website\Http\Resources\Tags\BlogPostTagResource;
use App\Website\Models\BlogPost;
use App\Website\Models\BlogPostTag;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;

class BlogPostTagsController extends Controller
{
    #[Endpoint(title: 'Blog Post Tags', description: 'This endpoint list all blog post tags in alphabetically order paginated by 20.')]
    #[Group('Website')]
    #[QueryParam('per_page', 'integer', required: false)]
    public function index(Request $request): AnonymousResourceCollection
    {
        $tags = BlogPostTag::query()
            ->orderBy('name', 'asc')
            ->paginate(min((int) ($request->query('per_page') ?: 20), 100));

        return BlogPostTagResource::collection($tags);
    }

    #[Endpoint(title: 'Blog Post By Tag', description: 'This endpoint retrieves blog posts by a specific tag.')]
    #[Group('Website')]
    #[QueryParam('per_page', 'integer', required: false)]
    public function show(Request $request, string $tag): AnonymousResourceCollection
    {
        $blogs = BlogPost::with('author', 'tags')
            ->where('status', BlogPostStatus::PUBLISHED)
            ->whereHas('tags', function ($query) use ($tag): void {
                $query->where('name', $tag);
            })
            ->orderBy('publish_date', 'desc')
            ->paginate(min((int) ($request->query('per_page') ?: 20), 100));

        return BlogPostsResource::collection($blogs);
    }
}
