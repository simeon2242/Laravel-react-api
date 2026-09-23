<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $categories = $request->user()
            ->categories()
            ->orderBy('name')
            ->get();

        return CategoryResource::collection($categories);
    }

    public function store(
        StoreCategoryRequest $request
    ): CategoryResource {
        $category = $request->user()
            ->categories()
            ->create($request->validated());

        return new CategoryResource($category);
    }

    public function update(
        UpdateCategoryRequest $request,
        int $category
    ): CategoryResource {
        $model = $request->user()
            ->categories()
            ->findOrFail($category);

        $model->update($request->validated());

        return new CategoryResource($model);
    }

    public function destroy(
        Request $request,
        int $category
    ): Response {
        $model = $request->user()
            ->categories()
            ->findOrFail($category);

        $model->delete();

        return response()->noContent();
    }
}