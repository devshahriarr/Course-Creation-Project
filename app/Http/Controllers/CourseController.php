<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CourseCategory;
use App\Models\Course;
use App\Models\Module;
use App\Models\Content;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Exception;

class CourseController extends Controller {

    public function create() {
        $categories = CourseCategory::all();
        $success = request()->get('success');
        if ($success) {
            return redirect()->route('courses.create')->with('success', $success);
        }
        return view('courses.create', compact('categories'));
    }

    // public function index() {
    //     $courses = Course::with(['category', 'modules.contents'])->get();
    //     return response()->json($courses);
    // }

    public function index() {
        $courses = Course::with(['course_category', 'modules.contents'])->paginate(10); // Optional: Add pagination
        // dd($courses);
        return view('courses.index', compact('courses'));
    }

    // public function storeCategory(Request $request) {
    //     $validator = Validator::make($request->all(), [
    //         'category_name' => 'required|string|max:255',
    //         'description' => 'nullable|string',
    //         'image' => 'nullable|image|max:2048',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['error' => $validator->errors()], 422);
    //     }

    //     try {
    //         $data = $request->only(['category_name', 'description']);
    //         if ($request->hasFile('image')) {
    //             $data['image'] = $request->file('image')->store('public/categories');
    //         }
    //         $category = CourseCategory::create($data);
    //         return response()->json(['success' => true, 'category' => $category]);
    //     } catch (Exception $e) {
    //         return response()->json(['error' => 'Failed to create category'], 500);
    //     }
    // }


    // public function create() {
    //     return view('courses.index');
    // }

    // public function store(Request $request) {
    //     // Backend validation
    //     $validator = Validator::make($request->all(), [
    //         'title' => 'required|string|max:255',
    //         'description' => 'nullable|string',
    //         'course_category_id' => 'required|exists:course_category,id',
    //         'modules' => 'required|array|min:1',
    //         'modules.*.title' => 'required|string|max:255',
    //         'modules.*.description' => 'nullable|string',
    //         'modules.*.contents' => 'required|array|min:1',
    //         'modules.*.contents.*.type' => 'required|in:text,image,video,link',
    //         'modules.*.contents.*.content' => 'required|string',
    //         'modules.*.contents.*.title' => 'nullable|string|max:255',
    //     ]);

    //     if ($validator->fails()) {
    //         return redirect()->back()
    //             ->withErrors($validator)
    //             ->withInput();
    //     }

    //     try {
    //         DB::beginTransaction();

    //         // Create course
    //         $course = Course::create($request->only(['title', 'description', 'course_category_id']));

    //         // Create modules and contents
    //         foreach ($request->input('modules') as $moduleData) {
    //             $module = $course->modules()->create($moduleData['title'] ? ['title' => $moduleData['title'], 'description' => $moduleData['description'] ?? null] : []);

    //             if (isset($moduleData['contents'])) {
    //                 foreach ($moduleData['contents'] as $contentData) {
    //                     $module->contents()->create($contentData);
    //                 }
    //             }
    //         }

    //         DB::commit();

    //         return redirect()->back()->with('success', 'Course created successfully!');
    //     } catch (Exception $e) {
    //         DB::rollBack();
    //         return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
    //     }
    // }

    public function store(Request $request) {
        // Backend validation
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'course_category_id' => 'required|exists:course_category,id',
            'image' => 'nullable|image|max:2048',
            'feature_image' => 'nullable|image|max:2048',
            'feature_video' => 'nullable|video|max:10240', // 10MB
            'price' => 'required|numeric|min:0',
            'modules' => 'required|array|min:1',
            'modules.*.title' => 'required|string|max:255',
            'modules.*.description' => 'nullable|string',
            'modules.*.contents' => 'required|array|min:1',
            'modules.*.contents.*.type' => 'required|in:text,image,video,link',
            'modules.*.contents.*.content' => 'required|string',
            'modules.*.contents.*.title' => 'nullable|string|max:255',
            // Nested files
            'modules.*.contents.*.content_file' => 'nullable|file|max:5120', // 5MB per content file
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            // Handle course files
            $courseData = $request->only(['title', 'description', 'course_category_id', 'price']);
            if ($request->hasFile('image')) {
                $courseData['image'] = $request->file('image')->store('public/courses');
            }
            if ($request->hasFile('feature_image')) {
                $courseData['feature_image'] = $request->file('feature_image')->store('public/courses');
            }
            if ($request->hasFile('feature_video')) {
                $courseData['feature_video'] = $request->file('feature_video')->store('public/videos');
            }

            $course = Course::create($courseData);

            // Create modules and contents with file handling
            foreach ($request->input('modules') as $i => $moduleData) {
                $module = $course->modules()->create([
                    'title' => $moduleData['title'],
                    'description' => $moduleData['description'] ?? null
                ]);

                if (isset($moduleData['contents'])) {
                    foreach ($moduleData['contents'] as $j => $contentData) {
                        $contentDataArr = $contentData;
                        $fileKey = "modules.{$i}.contents.{$j}.content_file";
                        if (in_array($contentData['type'], ['image', 'video']) && $request->hasFile($fileKey)) {
                            $path = $request->file($fileKey)->store("public/contents/{$contentData['type']}");
                            $contentDataArr['content'] = $path;
                        }
                        $module->contents()->create($contentDataArr);
                    }
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'Course created successfully!');
            // return response()->json(['success' => 'Course created successfully!', 'course_id' => $course->id]);
        } catch (Exception $e) {
            DB::rollBack();
            // return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }
}