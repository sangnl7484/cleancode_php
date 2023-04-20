<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Api\Product;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class ProductController extends Controller
{

    public function create(Request $request)
    {
        $data = $request->all();
        $data['created_by'] = $request->user()->id;
        $data['updated_by'] = $request->user()->id;
       
        $image = $data['image'] ?$data['image']: null;
        //
        if ($image) {
            $path = 'images';
            if(!App::environment('production')){
                $path = 'images/dev';
            }
           
            $relativePath = Storage::putFile('public/'.$path, $image);

            $data['image'] = URL::to(Storage::url($relativePath));
            $data['image_mime'] = $image->getClientMimeType();
            $data['image_size'] = $image->getSize();
        }

        $product = Product::create($data);

        $results = [
            'id' => $product->id,
            'title' => $product->title,
            'slug' => $product->slug,
            'description' => $product->description,
            'image_url' => $product->image ?: null,
            'price' => $product->price,
            'published' => (bool)$product->published,
            'created_at' => (new \DateTime($product->created_at))->format('Y-m-d H:i:s'),
            'updated_at' => (new \DateTime($product->updated_at))->format('Y-m-d H:i:s'),
        ];
      
        return response($results);
    }

    public function update(Request $request)
    {
        $data = $request->all();
        $data['updated_by'] = $request->user()->id;

        $image = $data['image'] ?$data['image']: null;
        $title = $data['title'] ?$data['title']: null;

        if ($image) {
            $path = 'images';
            if(!App::environment('production')){
                $path = 'images/dev';
            }
           
            $relativePath = Storage::putFile('public/' . $path, $image);

            $data['image'] = URL::to(Storage::url($relativePath));
            $data['image_mime'] = $image->getClientMimeType();
            $data['image_size'] = $image->getSize();
        }

        $product = Product::find($request->id);
        $product->update($data);
    
        $results = [
            'id' => $product->id,
            'title' => $product->title,
            'slug' => $product->slug,
            'description' => $product->description,
            'image_url' => $product->image ?: null,
            'price' => $product->price,
            'published' => (bool)$product->published,
            'created_at' => (new \DateTime($product->created_at))->format('Y-m-d H:i:s'),
            'updated_at' => (new \DateTime($product->updated_at))->format('Y-m-d H:i:s'),
        ];

        return response($results);
    }

    public function delete(Request $request)
    {
        $id = $request->id;
        $product = Product::find($id)->delete();

        return response()->noContent();
    }
}
