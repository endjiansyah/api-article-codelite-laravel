<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ArticleController extends Controller
{
    function index(Request $request)
    {
        $article = Article::query()
        ->leftjoin('category', 'article.id_category', '=', 'category.id')
        ->select('article.*','category.name as article_category');
        
        if ($request->has('category')) {
            $article->where('article.id_category', $request->category);
        }

        $page = $request->get('page', 1);
        $limit = $request->get('limit',-1);
        $offset = ($page - 1) * $limit;

        $articles = $article->offset($offset)->limit($limit)->get();

        return response()->json([
            "status" => true,
            "message" => "list article",
            "data" => $articles
        ]);
    }

    function show($id)
    {
        $article = Article::query()
            ->select('article.*','category.name as article_category')
            ->where("article.id", $id)
            ->leftjoin('category', 'article.id_category', '=', 'category.id')
            ->first();

        if (!isset($article)) {
            return response()->json([
                "status" => false,
                "message" => "data not found",
                "data" => null
            ]);
        }
        $media = Media::query()->where("id_article", $id)->get();
        $article["media"] = $media;

        return response()->json([
            "status" => true,
            "message" => "article with id ".$id,
            "data" => $article
        ]);
    }

    function store(Request $request)
    {
        $payload = [
            "title" => $request->input("title"),
            "content" => $request->input("content"),
            "author" => $request->input("author"),
            "id_category" => $request->input("id_category"),
        ];
        $validator = Validator::make($payload, [
            "title" => 'required',
            "content" => 'required',
            "author" => 'required',
            "id_category" => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "message" => $validator->errors(),
                "data" => null
            ]);
        }
        $article = Article::query()->create($payload);
        $responsemedia = [];
        $files = $request->file('media');
        foreach ($files as $file) {
            $mime = $file->getClientMimeType();
            $mimetype = explode("/",$mime);
            if($mimetype[0] == "image"||$mimetype[0] == "video"||$mimetype[0] == "audio"){
                $filename = $file->hashName();
                $file->move("media", $filename);
                $path = $request->getSchemeAndHttpHost() . "/media/" . $filename;
                $payloadmedia = [
                    "media" => $path,
                    "id_article" => $article->id
                ];
                $media = Media::query()->create($payloadmedia);
                $responsemedia[]=$media;

            }
        }
        $article["media"] = $responsemedia;
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
        if(!isset($payload['title'])&&!isset($payload['author'])&&!isset($payload['content'])&&!isset($payload['id_category'])){
            return response()->json([
                "status" => false,
                "message" => "nothing has been changed",
                "data" => $article
            ]);
        }

        // //------------------
        // $validator = Validator::make($payload, [
        //     "media" => 'mimes:jpg,jpeg,png,heic',
        // ]);

        // if ($validator->fails()) {
        //     return response()->json([
        //         "status" => false,
        //         "message" => $validator->errors(),
        //         "data" => null
        //     ]);
        // }

        // if ($request->hasFile('media')) {
        //     $file = $request->file('media');
        //     $filename = $file->hashName();
        //     $file->move('media', $filename);
        //     $path = $request->getSchemeAndHttpHost() . '/media/' . $filename;
        //     $payload['media'] = $path;

        //     $mediapath = str_replace($request->getSchemeAndHttpHost(), '', $article->media);
        //     $media = public_path($mediapath);
        //     unlink($media);
        // }
        // //------------------


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
        $article->delete();
        
        $listmedia = [];
        $media = Media::query()->where("id_article", $id)->get();
        foreach($media as $data){
            $med = Media::query()->where("id", $data->id)->first();

            $mediapath = str_replace($request->getSchemeAndHttpHost(), '', $data->media);
            $media = public_path($mediapath);
            unlink($media);
            $med->delete();
            $listmedia[] = $med;

        }
        $article["media"] = $listmedia;

        return response()->json([
            "status" => true,
            "message" => "data deleted successfully",
            "data" => $article
        ]);
    }

    function ArticleMediaCreate(Request $request, $id)
    {
        $article = Article::query()
            ->where("id", $id)
            ->first();

        if (!isset($article)) {
            return response()->json([
                "status" => false,
                "message" => "data not found",
                "data" => null
            ]);
        }

        $payload = [
            "media" => $request->media,
            "id_article" => $id,
        ];

        // //------------------
        $validator = Validator::make($payload, [
            "media" => 'mimes:jpg,jpeg,png,heic',
            "id_article" => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "message" => $validator->errors(),
                "data" => null
            ]);
        }

            $file = $request->file('media');
            $filename = $file->hashName();
            $file->move('media', $filename);
            $path = $request->getSchemeAndHttpHost() . '/media/' . $filename;
            $payload['media'] = $path;
        
        // //------------------
        $media = Media::query()->create($payload);

        return response()->json([
            "status" => true,
            "message" => "media saved successfully",
            "data" => $media
        ]);
    }

    function showmedia($idmedia)
    {
        $media = Media::query()
            ->where("id", $idmedia)
            ->first();

        if (!isset($media)) {
            return response()->json([
                "status" => false,
                "message" => "data not found",
                "data" => null
            ]);
        }

        return response()->json([
            "status" => true,
            "message" => "media article with id ".$idmedia,
            "data" => $media
        ]);
    }

    function MediaUpdate(Request $request, $idmedia)
    {
        $media = Media::query()
            ->where("id", $idmedia)
            ->first();

        if (!isset($media)) {
            return response()->json([
                "status" => false,
                "message" => "media not found",
                "data" => null
            ]);
        }

        $payload = [
            "media" => $request->media,
            "id_article" => $media->id_article,
        ];

        // //------------------
        if(isset($request->media)){

            $validator = Validator::make($payload, [
                "media" => 'mimes:jpg,jpeg,png,heic',
                "id_article" => 'required'
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    "status" => false,
                    "message" => $validator->errors(),
                    "data" => null
                ]);
            }
    
                $file = $request->file('media');
                $filename = $file->hashName();
                $file->move('media', $filename);
                $path = $request->getSchemeAndHttpHost() . '/media/' . $filename;
                $payload['media'] = $path;
    
                $mediapath = str_replace($request->getSchemeAndHttpHost(), '', $media->media);
                $mediadel = public_path($mediapath);
                unlink($mediadel);
        }else{
            return response()->json([
                "status" => false,
                "message" => "nothing has been changed",
                "data" => $media
            ]);
        }
        
        // //------------------
            $media->fill($payload);
            $media->save();

        return response()->json([
            "status" => true,
            "message" => "media update successfully",
            "data" => $media
        ]);
    }

    function destroymedia(Request $request,$idmedia)
    {
        $media = Media::query()->where("id", $idmedia)->first();
        if (!isset($media)) {
            return response()->json([
                "status" => false,
                "message" => "data not found",
                "data" => null
            ]);
        }
        

            $mediapath = str_replace($request->getSchemeAndHttpHost(), '', $media->media);
            $mediadel = public_path($mediapath);
            unlink($mediadel);
            $media->delete();

        return response()->json([
            "status" => true,
            "message" => "data delete successfully",
            "data" => $media
        ]);
    }
}
