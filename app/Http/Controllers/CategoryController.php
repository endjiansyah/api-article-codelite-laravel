<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    function index()
    {
        $category = Category::query()->get();

        return response()->json([
            "status" => true,
            "message" => "list category",
            "data" => $category
        ]);
    }

    function show($id)
    {
        $category = Category::query()
            ->where("id", $id)
            ->first();

        if (!isset($category)) {
            return response()->json([
                "status" => false,
                "message" => "data not found",
                "data" => null
            ]);
        }

        return response()->json([
            "status" => true,
            "message" => "category with id ".$id,
            "data" => $category
        ]);
    }

    function store(Request $request)
    {
        $payload = $request->all();
        $validator = Validator::make($payload, [
            "name" => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "message" => $validator->errors(),
                "data" => null
            ]);
        }

        $category = Category::query()->create($payload);
        return response()->json([
            "status" => true,
            "message" => "data saved successfully",
            "data" => $category
        ]);
    }

    function update(Request $request, $id)
    {
        $category = Category::query()->where("id", $id)->first();
        if (!isset($category)) {
            return response()->json([
                "status" => false,
                "message" => "data not found",
                "data" => null
            ]);
        }

        $payload = $request->all();

        $category->fill($payload);
        $category->save();

        return response()->json([
            "status" => true,
            "message" => "data changes successfully saved",
            "data" => $category
        ]);
    }

    function destroy($id)
    {
        $category = Category::query()->where("id", $id)->first();
        if (!isset($category)) {
            return response()->json([
                "status" => false,
                "message" => "data kosong",
                "data" => null
            ]);
        }

        $category->delete();

        return response()->json([
            "status" => true,
            "message" => "data deleted successfully",
            "data" => $category
        ]);
    }
}
