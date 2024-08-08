<?php

namespace App\Content\Http\Controllers;

use App\Content\Http\Requests\BlogPosts\BlogPostTagsDeleteRequest;
use App\Content\Http\Requests\BlogPosts\BlogPostTagsRequest;
use App\Content\Http\Resources\BlogPosts\BlogPostTagResource;
use App\Framework\Http\Controllers\Controller;
use App\Website\Models\BlogPostTag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;

class BlogPostTagsController extends Controller
{
    #[Authenticated]
    #[Endpoint(title: 'List Blog post tag', description: 'List Blog Post Tags or you can use search query for searching by the name
    or sort direction based on whether the key starts with - ')]
    #[Group('Content')]
    #[QueryParam('sort', 'string', required: false, example: 'name, -name', enum: ['name'])]
    #[QueryParam('search', 'string', required: false, example: 'name')]
    public function index(Request $request)
    {
        $query = BlogPostTag::query()
            ->when(
                $request->search,
                function (Builder $query) use ($request) {
                    $query->where('name', 'like', "%{$request->search}%");
                }
            )
            ->when(
                $request->sort,
                function (Builder $query) use ($request) {
                    $sortColumn = $request->input('sort', 'name');

                    $sortDirection = Str::startsWith($sortColumn, '-') ? 'desc' : 'asc';
                    $sortColumn = ltrim($sortColumn, '-');

                    $query->orderBy($sortColumn, $sortDirection);

                }
            );

        return BlogPostTagResource::collection($query->paginate(20));
    }

    #[Authenticated]
    #[Endpoint(title: 'Create Blog post tag', description: 'This endpoint will create a single blog post tag')]
    #[Group('Content')]
    #[BodyParam('name', 'string', required: true, example: 'test')]
    public function store(BlogPostTagsRequest $request): BlogPostTagResource
    {
        $tag = BlogPostTag::create(['name' => $request->name]);

        return new BlogPostTagResource($tag);
    }

    #[Authenticated]
    #[Endpoint(title: 'Update Blog post tag', description: 'This endpoint will update a single blog post tag')]
    #[Group('Content')]
    #[BodyParam('name', 'string', required: true, example: 'test')]
    public function update(BlogPostTagsRequest $request, int $blogPostTag): BlogPostTagResource
    {
        $blogPostTag = BlogPostTag::findOrFail($blogPostTag);
        $blogPostTag->update(['name' => $request->name]);

        return new BlogPostTagResource($blogPostTag);
    }

    #[Authenticated]
    #[Endpoint(title: 'Delete Blog post tag', description: 'This endpoint deletes blog post tag')]
    #[Group('Content')]
    public function destroy(int $blogPostTag, BlogPostTagsDeleteRequest $request): Response
    {
        $blogPostTag = BlogPostTag::findOrFail($blogPostTag);
        $blogPostTag->delete();

        return response()->noContent();
    }
}
