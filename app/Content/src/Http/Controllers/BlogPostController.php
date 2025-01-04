<?php

namespace App\Content\Http\Controllers;

use App\Content\Http\Requests\BlogPosts\BlogPostPermissionsRequest;
use App\Content\Http\Requests\BlogPosts\CreateBlogPostRequest;
use App\Content\Http\Requests\BlogPosts\UpdateBlogPostRequest;
use App\Content\Http\Resources\BlogPosts\BlogPostsResource;
use App\Framework\Http\Controllers\Controller;
use App\Framework\Enums\BlogPostStatus;
use App\Content\Models\Author;
use App\Content\Models\BlogPost;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;

class BlogPostController extends Controller
{
    #[Authenticated]
    #[Endpoint(title: 'Blog posts', description: 'This endpoint list all blog post')]
    #[Group('Content')]
    #[QueryParam('title', 'string', required: false, example: 'learnhub')]
    #[QueryParam('status', 'string', required: false, example: 'published', enum: ['draft', 'archived', 'published', 'in_review'])]
    #[QueryParam('tags', 'string[]', required: false, example: ['php', 'javascript', 'mysql'])]
    #[QueryParam('author', 'string', required: false, example: 'john')]
    #[QueryParam('sort', 'string', required: false, example: 'title', enum: ['id', 'title', 'publish_date', 'created_at'])]
    #[QueryParam('per_page', 'integer', required: false)]
    public function index(BlogPostPermissionsRequest $request): AnonymousResourceCollection
    {
        $blogs = BlogPost::with('author', 'tags')
            ->when($request->get('title'), function ($query) use ($request) {
                return $query->where('title', 'like', "%{$request->title}%");
            })
            ->when($request->get('tags'), function ($query) use ($request) {
                $tags = explode(',', $request->tag);

                return $query->whereHas('tags', function ($tagQuery) use ($tags) {
                    $tagQuery->whereIn('name', $tags);
                });
            })
            ->when($request->get('author'), function ($query) use ($request) {
                return $query->whereHas('author', function ($authorQuery) use ($request) {
                    $authorQuery->where('first_name', 'like', "%$request->author%")
                        ->orWhere('last_name', 'like', "%$request->author%");
                });
            })
            ->when($request->get('status'), function ($query) use ($request) {
                $query->where('status', $request->get('status'));
            })
            ->when($request->get('sort'), function ($query) use ($request) {
                $query->orderByDesc($request->sort);
            })
            ->paginate(min((int) $request->query('per_page') ?? 20, 100));

        return BlogPostsResource::collection($blogs);
    }

    #[Authenticated]
    #[Endpoint(title: 'Create Blog posts', description: 'This endpoint will create a single blog post')]
    #[Group('Content')]
    #[BodyParam('title', 'string', required: true, example: 'Example Blog Post Title ')]
    #[BodyParam('excerpt', 'string', required: true, example: 'This is test blogpost example')]
    #[BodyParam('content', 'string', required: true, example: 'Lorem ipsum dolor sit amet, consectetur adipiscing ...')]
    #[BodyParam('tags', 'array', required: true, example: '[1,2,3]')]
    public function store(CreateBlogPostRequest $request): BlogPostsResource
    {
        $image = $request->file('image')?->storePubliclyAs('blog-post-images');

        $blogPost = BlogPost::create([
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'excerpt' => $request->excerpt,
            'content' => $request->get('content'),
            'status' => BlogPostStatus::DRAFT,
            'image' => $image,
            'author_id' => Author::query()->where('user_id', $request->user()->id)->value('id'),
        ]);

        $blogPost->tags()->sync($request->tags);

        return new BlogPostsResource($blogPost);
    }

    #[Authenticated]
    #[Endpoint(title: 'Blog post', description: 'This endpoint returns a single blog post')]
    #[Group('Content')]
    public function show(int $blogPost, BlogPostPermissionsRequest $request): BlogPostsResource
    {
        $blogPost = BlogPost::with(['author', 'tags'])->findOrFail($blogPost);

        return new BlogPostsResource($blogPost);
    }

    #[Authenticated]
    #[Endpoint(title: 'Update Blog post', description: 'This endpoint will update a single blog post')]
    #[Group('Content')]
    #[BodyParam('title', 'string', required: false, example: 'Example Blog Post Title ')]
    #[BodyParam('slug', 'string', required: false, example: 'example-blog-post-title ')]
    #[BodyParam('excerpt', 'string', required: false, example: 'This is test blogpost example')]
    #[BodyParam('content', 'string', required: false, example: 'Lorem ipsum dolor sit amet, consectetur adipiscing ...')]
    #[BodyParam('tags', 'array', required: false, example: '[1,2,3]')]
    public function update(UpdateBlogPostRequest $request, int $blogPost): BlogPostsResource
    {
        $blogPostData = $request->only(['title', 'slug', 'excerpt', 'content']);
        $image = $request->file('image')?->storePubliclyAs('blog-post-images');
        $blogPostData = $image ? array_merge(['image' => $image], $blogPostData) : $blogPostData;

        $blogPost = BlogPost::findOrFail($blogPost);
        $blogPost->update(array_merge($blogPostData));

        if(Storage::has($blogPost->image)) {
            Storage::delete($blogPost->image);
        }

        if ($request->get('tags')) {
            $blogPost->tags()->sync($request->tags);
        }

        return new BlogPostsResource($blogPost);

    }

    #[Authenticated]
    #[Endpoint(title: 'Delete Blog posts', description: 'This endpoint deletes blog post')]
    #[Group('Content')]
    public function destroy(int $blogPost, BlogPostPermissionsRequest $request): Response
    {
        $blogPost = BlogPost::findOrFail($blogPost);

        if(Storage::has($blogPost->image)) {
            Storage::delete($blogPost->image);
        }

        $blogPost->delete();

        return response()->noContent();
    }
}
