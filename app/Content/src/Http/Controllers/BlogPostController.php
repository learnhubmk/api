<?php

namespace App\Content\Http\Controllers;

use App\Content\Http\Requests\BlogPosts\BlogPostPermissionsRequest;
use App\Content\Http\Requests\BlogPosts\CreateBlogPostRequest;
use App\Content\Http\Resources\BlogPosts\BlogPostsResource;
use App\Http\Controllers\Controller;
use App\Website\Enums\BlogPostStatus;
use App\Website\Models\Author;
use App\Website\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;

class BlogPostController extends Controller
{
    #[Endpoint(title: 'Blog posts', description: 'This endpoint list all blog post')]
    #[Group('Content')]
    #[QueryParam('title', 'string', required: false, example: "?title=learnhub")]
    #[QueryParam('tag', 'string', required: false, example: "?tag=php")]
    #[QueryParam('author', 'string', required: false, example: "?author=john")]
    #[QueryParam('sort', 'array', required: false, example: "?sort=[id,title]", enum: ['id', 'title', 'publish_date', 'created_at'])]
    public function index(BlogPostPermissionsRequest $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $blogs = BlogPost::with('author', 'tags')
            ->when($request->has('title'), function ($query) use ($request) {
                return $query->where('title', 'like', "%{$request->title}%");
            })
            ->when($request->has('tag'), function ($query) use ($request) {
                return $query->whereHas('tags', function ($tagQuery) use ($request) {
                    $tagQuery->where('name', 'like',  "%$request->tag%");
                });
            })
            ->when($request->has('author'), function ($query) use ($request) {
                return $query->whereHas('author', function ($authorQuery) use ($request) {
                    $authorQuery->where('first_name', 'like', "%$request->author%")
                    ->orWhere('last_name', 'like', "%$request->author%" );
                });
            })
            ->when($request->has('sort'), function ($query) use ($request) {
                $sortFields = explode(',', str_replace(['[', ']'], '', $request->sort));
                foreach ($sortFields as $sortField) {
                    $direction = 'desc';
                    $query->orderBy($sortField, $direction);
                }
            })
            ->paginate(15);

        return BlogPostsResource::collection($blogs);
    }

    #[Endpoint(title: 'Create Blog posts', description: 'This endpoint will create a dingle blog post')]
    #[Group('Content')]
    #[BodyParam('title', 'string', required: true, example: "Example Blog Post Title ")]
    #[BodyParam('excerpt', 'string', required: true, example: "This is test blogpost example")]
    #[BodyParam('content', 'string', required: true, example: "Lorem ipsum dolor sit amet, consectetur adipiscing ...")]
    #[BodyParam('tags', 'array', required: true, example: "[1,2,3]")]
    public function store(CreateBlogPostRequest $request): BlogPostsResource
    {
        $blogPost = BlogPost::create([
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'excerpt' => $request->excerpt,
            'content' => $request->content,
            'status' => BlogPostStatus::IN_REVIEW,
            'author_id' => Author::where('user_id',$request->user()->id)->value('id')
        ]);

        $blogPost->tags()->sync($request->tags);

        return new BlogPostsResource($blogPost);
    }

    #[Endpoint(title: 'Blog post', description: 'This endpoint returns a single blog post')]
    #[Group('Content')]
    public function show(BlogPost $blog_post, BlogPostPermissionsRequest $request) : BlogPostsResource
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
    #[Group('Content')]
    public function destroy(BlogPost $blog_post, BlogPostPermissionsRequest $request): \Illuminate\Http\Response
    {
        $blog_post->delete();

        return response()->noContent();
    }
}
