<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ArticleController extends Controller
{
    function index()
    {
        $article = Article::query()
        ->join('category', 'article.id_category', '=', 'category.id')
        ->select('article.*','category.name as article_category')
        ->get();

        return response()->json([
            "status" => true,
            "message" => "list article",
            "data" => $article
        ]);
    }

    function show($id)
    {
        $article = Article::query()
            ->select('article.*','category.name as article_category')
            ->where("article.id", $id)
            ->join('category', 'article.id_category', '=', 'category.id')
            ->first();

        if (!isset($article)) {
            return response()->json([
                "status" => false,
                "message" => "data not found",
                "data" => null
            ]);
        }

        return response()->json([
            "status" => true,
            "message" => "article with id ".$id,
            "data" => $article
        ]);
    }

    function store(Request $request)
    {
        $payload = $request->all();
        $validator = Validator::make($payload, [
            "title" => 'required',
            "content" => 'required',
            "media" => 'required|mimes:jpg,jpeg,png,heic',
            "id_category" => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "message" => $validator->errors(),
                "data" => null
            ]);
        }

        //-----
        $file = $request->file("media");
        $filename = $file->hashName();
        $file->move("media", $filename);
        $path = $request->getSchemeAndHttpHost() . "/media/" . $filename;
        $payload['media'] =  $path;
         //----

        $article = Article::query()->create($payload);
        return response()->json([
            "status" => true,
            "message" => "data saved successfully",
            "data" => $article
        ]);
    }

    function update(Request $request, $id)
    {
        $article = Article::query()->where("id", $id)->first();
        if (!isset($article)) {
            return response()->json([
                "status" => false,
                "message" => "data not found",
                "data" => null
            ]);
        }

        $payload = $request->all();
        if(!isset($payload['title'])&&!isset($payload['media'])&&!isset($payload['content'])){
            return response()->json([
                "status" => false,
                "message" => "nothing has been changed",
                "data" => $article
            ]);
        }
        $validator = Validator::make($payload, [
            "media" => 'mimes:jpg,jpeg,png,heic',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "message" => $validator->errors(),
                "data" => null
            ]);
        }

        if ($request->hasFile('media')) {
            $file = $request->file('media');
            $filename = $file->hashName();
            // $file->move('media', $filename);
            $path = $request->getSchemeAndHttpHost() . '/media/' . $filename;
            $payload['media'] = $path;

            $mediapath = str_replace($request->getSchemeAndHttpHost(), '', $article->media);
            $media = public_path($mediapath);
            unlink($media);
        }

        $article->fill($payload);
        $article->save();

        return response()->json([
            "status" => true,
            "message" => "data changes successfully saved",
            "data" => $article
        ]);
    }

    function destroy(Request $request,$id)
    {
        $article = Article::query()->where("id", $id)->first();
        if (!isset($article)) {
            return response()->json([
                "status" => false,
                "message" => "data not found",
                "data" => null
            ]);
        }
        if($article->media != ''){
            $mediapath = str_replace($request->getSchemeAndHttpHost(), '', $article->media);
                $media = public_path($mediapath);
                unlink($media);
        }

        $article->delete();

        return response()->json([
            "status" => true,
            "message" => "data deleted successfully",
            "data" => $article
        ]);
    }
}
