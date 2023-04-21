<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\Api\Product;
use App\Helpers\HelperAPI;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class ProductController extends Controller
{
    /**
     * @param Request $request
     * @return array
     */
    public function create(ProductRequest $request): array
    {
        $data = $request->all();
        try {
            $data['created_by'] = $request->user()->id;
            $data['updated_by'] = $request->user()->id;
            if (isset($data['image'])) {
                $data = array_merge($this->renderImage($data['image']), $data);
            }
            $product = Product::create($data);
        } catch (\Throwable $e) {
            return HelperAPI::responseError($e->getMessage());
        }

        return HelperAPI::responseSuccess($this->getProduct($product));
    }

    /**
     * @param Request $request
     * @return array
     */
    public function update(ProductRequest $request)
    {
        $data = $request->all();

        try {
            $data['updated_by'] = $request->user()->id;
            if (isset($data['image'])) {
                $data = array_merge($this->renderImage($data['image']), $data);
            }
            Product::find($data['id'])->update($data);
        } catch (\Throwable $e) {
            return HelperAPI::responseError($e->getMessage());
        }

        return HelperAPI::responseSuccess();
    }

    /**
     * @param Request $request
     * @return array
     */
    public function delete(Request $request): array
    {
        try {
            Product::find($request->id)->delete();
        } catch (\Throwable $e) {
            return HelperAPI::responseError($e->getMessage());
        }

        return HelperAPI::responseSuccess();
    }

    /**
     * @param Product $product
     * @return array
     */
    public function getProduct(Product $product): array
    {
        return [
            'id' => $product->id,
            'title' => $product->title,
            'slug' => $product->slug,
            'description' => $product->description,
            'image_url' => $product->image ?: null,
            'price' => $product->price,
            'published' => (bool)$product->published,
            'created_at' => HelperAPI::formatDateTime($product->created_at),
            'updated_at' => HelperAPI::formatDateTime($product->updated_at)
        ];
    }

    /**
     * @param $image
     * @return array
     */
    public function renderImage($image): array
    {
        $data = [];
        $path = 'images';
        if (!App::environment('production')) {
            $path = 'images/dev';
        }

        $relativePath = Storage::putFile('public/' . $path, $image);

        $data['image'] = URL::to(Storage::url($relativePath));
        $data['image_mime'] = $image->getClientMimeType();
        $data['image_size'] = $image->getSize();

        return $data;
    }
}
