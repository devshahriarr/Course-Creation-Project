<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Module;
use App\Models\Content;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Exception;

class CourseController extends Controller {
    public function create() {
        return view('courses.index');
    }

    public function store(Request $request) {
        // Backend validation
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'course_category_id' => 'required|exists:course_category,id',
            'modules' => 'required|array|min:1',
            'modules.*.title' => 'required|string|max:255',
            'modules.*.description' => 'nullable|string',
            'modules.*.contents' => 'required|array|min:1',
            'modules.*.contents.*.type' => 'required|in:text,image,video,link',
            'modules.*.contents.*.content' => 'required|string',
            'modules.*.contents.*.title' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Create course
            $course = Course::create($request->only(['title', 'description', 'course_category_id']));

            // Create modules and contents
            foreach ($request->input('modules') as $moduleData) {
                $module = $course->modules()->create($moduleData['title'] ? ['title' => $moduleData['title'], 'description' => $moduleData['description'] ?? null] : []);

                if (isset($moduleData['contents'])) {
                    foreach ($moduleData['contents'] as $contentData) {
                        $module->contents()->create($contentData);
                    }
                }
            }

            DB::commit();

            return redirect()->back()->with('success', 'Course created successfully!');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }
}