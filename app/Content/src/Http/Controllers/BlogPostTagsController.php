<?php

namespace App\Content\Http\Controllers;

use App\Content\Http\Requests\BlogPosts\BlogPostTagsDeleteRequest;
use App\Content\Http\Requests\BlogPosts\BlogPostTagsRequest;
use App\Content\Http\Resources\BlogPosts\BlogPostTagResource;
use App\Framework\Http\Controllers\Controller;
use App\Website\Models\BlogPostTag;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;

class BlogPostTagsController extends Controller
{
    #[Endpoint(title: 'List Blog post tag', description: 'List Blog Post Tags in Ascending Order')]
    #[Group('Content')]
    public function index(): AnonymousResourceCollection
    {
        $tags = BlogPostTag::orderBy('name', 'asc')->paginate(20);

        return BlogPostTagResource::collection($tags);
    }

    #[Endpoint(title: 'Create Blog post tag', description: 'This endpoint will create a single blog post tag')]
    #[Group('Content')]
    #[BodyParam('name', 'string', required: true, example: "test")]
    public function store(BlogPostTagsRequest $request): BlogPostTagResource
    {
        $tag = BlogPostTag::create(['name' => $request->name]);

        return new BlogPostTagResource($tag);
    }


    #[Endpoint(title: 'Update Blog post tag', description: 'This endpoint will update a single blog post tag')]
    #[Group('Content')]
    #[BodyParam('name', 'string', required: true, example: "test")]
    public function update(BlogPostTagsRequest $request, BlogPostTag $blogPostTag): BlogPostTagResource
    {
        $blogPostTag->update(['name' => $request->name]);

        return new BlogPostTagResource($blogPostTag);
    }


    #[Endpoint(title: 'Delete Blog post tag', description: 'This endpoint deletes blog post tag')]
    #[Group('Content')]
    public function destroy(BlogPostTag $blogPostTag, BlogPostTagsDeleteRequest $request): \Illuminate\Http\Response
    {
        $blogPostTag->delete();

        return response()->noContent();
    }
}
