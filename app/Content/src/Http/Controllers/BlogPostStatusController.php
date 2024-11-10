<?php

namespace App\Content\Http\Controllers;

use App\Content\Http\Requests\BlogPosts\BlogPostPermissionsRequest;
use App\Content\Models\BlogPost;
use Illuminate\Http\Response;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;

class BlogPostStatusController
{
    #[Authenticated]
    #[Endpoint(title: 'Publish/Unpublish Blog posts', description: 'This endpoint publish or unpublish blog post')]
    #[BodyParam('publish_date', 'date', required: false, example: '2024-01-01')]
    #[BodyParam('status', 'string', required: true, example: 'draft, published, in_review, archive')]
    #[Group('Content')]
    public function update(int $blogPost, BlogPostPermissionsRequest $request): Response
    {
        $blogPost = BlogPost::findOrFail($blogPost);
        $blogPost->update(['status' => $request->status, 'publish_date' => $request->publish_date ?? null]);

        return response()->noContent();
    }
}
