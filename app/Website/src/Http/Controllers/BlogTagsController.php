<?php

namespace App\Website\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Website\Http\Resources\Tags\BlogPostTagResource;
use App\Website\Models\BlogPostTag;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\QueryParam;

class BlogTagsController extends Controller
{
    #[Endpoint('Website/List Blog Post Tags', <<<'DESC'
              This endpoint list all blogpost tags in alphabetically order paginated by 20.
              Additionally you may use ?name= query param to search by name
    DESC)]
    #[QueryParam('name', 'string', required: false)]
    public function index(Request $request): AnonymousResourceCollection
    {
        $tags = BlogPostTag::when($request->has('name'), function ($query) use ($request) {
            return $query->where('name', 'like', "{$request->name}%");
        })->orderBy('name', 'asc')->paginate(20);

        return BlogPostTagResource::collection($tags);
    }

    // TODO: In future we may use other methods to create/update tags
}
