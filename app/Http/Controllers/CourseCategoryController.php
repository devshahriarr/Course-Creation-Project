<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CourseCategory;
use Illuminate\Support\Facades\Validator;
use Exception;
class CourseCategoryController extends Controller
{
    public function storeCategory(Request $request) {
        $validator = Validator::make($request->all(), [
            'category_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            $data = $request->only(['category_name', 'description']);
            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('public/categories');
            }
            $category = CourseCategory::create($data);
            return response()->json(['success' => true, 'category' => $category]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to create category'], 500);
        }
    }

}
