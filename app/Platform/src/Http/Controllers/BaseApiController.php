<?php

namespace App\Platform\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Arr;
use Illuminate\Http\Response;

class BaseApiController extends Controller
{
    /**
     * Function responsible for generating JSON response
     * @param array $data
     * @param array $meta
     * @param int $status
     * @param string $message
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function response($data = [], array $meta = [], int $status = 200, string $message = 'Success'): Response
    {
        $response = [
            'meta' => [
                'status' => $status,
                'message' => $message,
            ],
        ];

        if (!empty($meta)) {
            $response['meta'] = array_merge($response['meta'], $meta);
        }

        if ($data instanceof AnonymousResourceCollection) {
            $response['meta']['pagination'] = Arr::except($data->resource->toArray(), 'data');
        }

        $response['data'] = $data;

        return response($response, $status);
    }
}
