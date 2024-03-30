<?php

namespace App\Website\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Website\Enums\BlogPostStatus;
use App\Website\Http\Resources\Blogs\BlogPostsResource;
use App\Website\Http\Resources\Tags\BlogPostTagResource;
use App\Website\Models\BlogPost;
use App\Website\Models\BlogPostTag;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Knuckles\Scribe\Attributes\Endpoint;

class BlogPostTagsController extends Controller
{
    #[Endpoint('Website/List Blog Post Tags', <<<'DESC'
              This endpoint list all blogpost tags in alphabetically order paginated by 20.
    DESC)]
    public function index(Request $request): AnonymousResourceCollection
    {
        $tags = BlogPostTag::orderBy('name', 'asc')->paginate(20);

        return BlogPostTagResource::collection($tags);
    }

    #[Endpoint('Website/Get Blog Post By Tag', <<<'DESC'
  This endpoint retrieve blogpost by a specific tag.
  DESC)]
    public function show(string $tag): AnonymousResourceCollection
    {
        $blogs = BlogPost::with('author', 'tags')
            ->where('status', BlogPostStatus::PUBLISHED)
            ->whereHas('tags', function ($query) use ($tag) {
                $query->where('name', $tag);
            })
            ->orderBy('publish_date', 'desc')
            ->paginate(15);

        return BlogPostsResource::collection($blogs);
    }
}
