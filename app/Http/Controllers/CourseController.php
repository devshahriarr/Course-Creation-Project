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


    public function index() {
        $courses = Course::with(['course_category', 'modules.contents'])->paginate(10);
        // dd($courses);
        return view('courses.index', compact('courses'));
    }


    // public function store(Request $request) {
    //     // Backend validation

    //     // dd($request);
    //     $validator = Validator::make($request->all(), [
    //         'title' => 'required|string|max:255',
    //         'description' => 'required|string',
    //         'course_category_id' => 'required|exists:course_category,id',
    //         'feature_image' => 'nullable|image|max:2048',
    //         'feature_video' => 'nullable|video|max:10240', // 10MB
    //         'price' => 'required|numeric|min:0',
    //         'modules' => 'required|array|min:1',
    //         'modules.*.title' => 'required|string|max:255',
    //         'modules.*.description' => 'nullable|string',
    //         'modules.*.contents' => 'required|array|min:1',
    //         'modules.*.contents.*.type' => 'required|in:text,image,video,link',
    //         'modules.*.contents.*.title' => 'nullable|string|max:255',
    //         // Nested files
    //         // Handle content differently based on type
    //         'modules.*.contents.*.content' => 'required_if:modules.*.contents.*.type,text,link|string',
    //         'modules.*.contents.*.content_file' => 'nullable|file|max:5120|required_if:modules.*.contents.*.type,image,video',
    //     ]);

    //     // if ($validator->fails()) {
    //     //     // return response()->json(['error' => $validator->errors()], 422);

    //     //     return redirect()->back()->with('error' . $validator->errors());
    //     // }

    //     if ($validator->fails()) {
    //         return redirect()->back()->with('errors', $validator->errors())->withInput();
    //     }

    //     try {
    //         DB::beginTransaction();

    //         // Handle course files
    //         $courseData = $request->only(['title', 'description', 'course_category_id', 'price']);
    //         if ($request->hasFile('image')) {
    //             $courseData['image'] = $request->file('image')->store('public/courses');
    //         }
    //         if ($request->hasFile('feature_image')) {
    //             $courseData['feature_image'] = $request->file('feature_image')->store('public/courses');
    //         }
    //         if ($request->hasFile('feature_video')) {
    //             $courseData['feature_video'] = $request->file('feature_video')->store('public/videos');
    //         }

    //         $course = Course::create($courseData);

    //         // Create modules and contents with file handling
    //         foreach ($request->input('modules') as $i => $moduleData) {
    //             $module = $course->modules()->create([
    //                 'title' => $moduleData['title'],
    //                 'description' => $moduleData['description'] ?? null
    //             ]);

    //             if (isset($moduleData['contents'])) {
    //                 foreach ($moduleData['contents'] as $j => $contentData) {
    //                     $contentDataArr = $contentData;
    //                     $fileKey = "modules.{$i}.contents.{$j}.content_file";
    //                     if (in_array($contentData['type'], ['image', 'video']) && $request->hasFile($fileKey)) {
    //                         $path = $request->file($fileKey)->store("public/contents/{$contentData['type']}");
    //                         $contentDataArr['content'] = $path;
    //                     }
    //                     $module->contents()->create($contentDataArr);
    //                 }
    //             }
    //         }

    //         DB::commit();
    //         return redirect()->back()->with('success', 'Course created successfully!');
    //         // return response()->json(['success' => 'Course created successfully!', 'course_id' => $course->id]);
    //     } catch (Exception $e) {
    //         DB::rollBack();
    //         // return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
    //         return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
    //     }
    // }

    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'course_category_id' => 'required|exists:course_category,id',
            'feature_image' => 'nullable|image|max:2048',
            'feature_video' => 'nullable|video|max:10240',
            'price' => 'required|numeric|min:0',
            'modules' => 'required|array|min:1',
            'modules.*.title' => 'required|string|max:255',
            'modules.*.description' => 'nullable|string',
            'modules.*.contents' => 'required|array|min:1',
            'modules.*.contents.*.type' => 'required|in:text,image,video,link',
            'modules.*.contents.*.title' => 'nullable|string|max:255',
            'modules.*.contents.*.content' => 'required_if:modules.*.contents.*.type,text,link|string',
            'modules.*.contents.*.content_file' => 'nullable|file|max:5120|required_if:modules.*.contents.*.type,image,video',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            // Handle course files
            $courseData = $request->only(['title', 'description', 'course_category_id', 'price']);
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
                        } else if (!in_array($contentData['type'], ['image', 'video'])) {
                            $contentDataArr['content'] = $contentData['content'] ?? '';
                        }
                        $module->contents()->create($contentDataArr);
                    }
                }
            }

            DB::commit();
            return redirect()->route('courses.index')->with('success', 'Course created successfully!');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage())->withInput();
        }
    }
}