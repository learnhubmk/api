<?php

namespace App\Content\Http\Controllers;

use App\Content\Http\Requests\BlogPosts\BlogPostPermissionsRequest;
use App\Content\Http\Requests\BlogPosts\CreateBlogPostRequest;
use App\Content\Http\Requests\BlogPosts\UpdateBlogPostRequest;
use App\Content\Http\Resources\BlogPosts\BlogPostsResource;
use App\Framework\Http\Controllers\Controller;
use App\Website\Enums\BlogPostStatus;
use App\Website\Models\Author;
use App\Website\Models\BlogPost;
use Illuminate\Support\Collection;
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
    #[QueryParam('tags', 'array', required: false, example: "?tags=[php,laravel,react]")]
    #[QueryParam('author', 'string', required: false, example: "?author=john")]
    #[QueryParam('sort', 'string', required: false, example: "?sort=title", enum: ['id', 'title', 'publish_date', 'created_at'])]
    public function index(BlogPostPermissionsRequest $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $blogs = BlogPost::with('author', 'tags')
            ->when($request->has('title'), function ($query) use ($request) {
                return $query->where('title', 'like', "%{$request->title}%");
            })
            ->when($request->has('tags'), function ($query) use ($request) {
                $tags = explode(',', $request->tag);
                return $query->whereHas('tags', function ($tagQuery) use ($tags) {
                    $tagQuery->whereIn('name', $tags);
                });
            })
            ->when($request->has('author'), function ($query) use ($request) {
                return $query->whereHas('author', function ($authorQuery) use ($request) {
                    $authorQuery->where('first_name', 'like', "%$request->author%")
                    ->orWhere('last_name', 'like', "%$request->author%");
                });
            })
            ->when($request->has('sort'), function ($query) use ($request) {
                $query->orderByDesc($request->sort);
            })
            ->paginate(15);

        return BlogPostsResource::collection($blogs);
    }

    #[Endpoint(title: 'Create Blog posts', description: 'This endpoint will create a single blog post')]
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
            'author_id' => Author::where('user_id', $request->user()->id)->value('id')
        ]);

        $blogPost->tags()->sync($request->tags);

        return new BlogPostsResource($blogPost);
    }

    #[Endpoint(title: 'Blog post', description: 'This endpoint returns a single blog post')]
    #[Group('Content')]
    public function show(BlogPost $blogPost, BlogPostPermissionsRequest $request): BlogPostsResource
    {
        $blogPost->load('author', 'tags');

        return new BlogPostsResource($blogPost);
    }

    #[Endpoint(title: 'Update Blog post', description: 'This endpoint will update a single blog post')]
    #[Group('Content')]
    #[BodyParam('title', 'string', required: false, example: "Example Blog Post Title ")]
    #[BodyParam('slug', 'string', required: false, example: "example-blog-post-title ")]
    #[BodyParam('excerpt', 'string', required: false, example: "This is test blogpost example")]
    #[BodyParam('content', 'string', required: false, example: "Lorem ipsum dolor sit amet, consectetur adipiscing ...")]
    #[BodyParam('tags', 'array', required: false, example: "[1,2,3]")]
    public function update(UpdateBlogPostRequest $request, BlogPost $blogPost): BlogPostsResource
    {
        $data = $request->validated();

        $blogPostData = (new Collection($data))->except('tags')->all();

        $blogPost->update($blogPostData);

        if ($request->has('tags')) {
            $blogPost->tags()->sync($request->tags);
        }

        return new BlogPostsResource($blogPost);

    }

    #[Endpoint(title: 'Delete Blog posts', description: 'This endpoint deletes blog post')]
    #[Group('Content')]
    public function destroy(BlogPost $blogPost, BlogPostPermissionsRequest $request): \Illuminate\Http\Response
    {
        $blogPost->delete();

        return response()->noContent();
    }

}
