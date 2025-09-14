<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Course</title>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        body {
            background: #e8e8ea;
            color: #000000;
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #ffffff;
            padding: 20px 30px;
            border-radius: 8px;
        }
        input, textarea, select {
            width: 100%;
            padding: 8px;
            margin: 5px 0;
            background: #ffffff;
            color: #262424;
            border: 1px solid #533483;
            border-radius: 4px;
        }
        .module {
            background: #ffffff;
            margin: 10px 0;
            padding: 15px;
            border-radius: 4px;
            border-left: 4px solid #29b74a;
        }
        .content {
            background: #d2d3d7;
            margin: 10px 0;
            padding: 10px 25px 10px 10px;
            border-radius: 4px;
            border-left: 4px solid #533483;
        }
        button {
            background: #28a745;
            color: #fff;
            border: none;
            padding: 8px 12px;
            cursor: pointer;
            border-radius: 4px;
            margin: 5px;
        }
        button:hover {
            background: #218b39;
        }
        .error {
            color: #e94560;
            font-size: 0.9em;
        }
        .success {
            color: #28a745;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Create a New Course</h1>

        @if (session('success'))
            <p class="success">{{ session('success') }}</p>
        @endif
        @if (session('error'))
            <p class="error">{{ session('error') }}</p>
        @endif

        <form method="POST" action="{{ route('courses.store') }}">
            @csrf

            <!-- Course Fields -->
            <h2>Course Details</h2>
            <input type="text" name="title" placeholder="Course Title" value="{{ old('title') }}" required>
            @error('title') <span class="error">{{ $message }}</span> @enderror

            <textarea name="description" placeholder="Description" rows="3">{{ old('description') }}</textarea>
            @error('description') <span class="error">{{ $message }}</span> @enderror

            <input type="text" name="course_category_id" placeholder="Category" value="{{ old('course_category_id') }}">
            @error('course_category_id') <span class="error">{{ $message }}</span> @enderror

            <!-- Modules Section -->
            <h2>Modules</h2>
            <button type="button" id="addModule">Add Module</button>
            <div id="modules">
                <!-- Dynamic modules added here -->
            </div>

            <button type="submit">Save Course</button>
        </form>
    </div>

    <script>
        $(document).ready(function() {
            let moduleCount = 0;

            $('#addModule').click(function() {
                moduleCount++;
                const moduleHtml = `
                    <div class="module" data-module="${moduleCount}">
                        <h3>Module ${moduleCount}</h3>
                        <input type="text" name="modules[${moduleCount}][title]" placeholder="Module Title" required>
                        <textarea name="modules[${moduleCount}][description]" placeholder="Module Description" rows="2"></textarea>
                        <button type="button" class="addContent" data-module="${moduleCount}">Add Content</button>
                        <button type="button" class="removeModule">Remove Module</button>
                        <div class="contents" data-module="${moduleCount}">
                            <!-- Dynamic contents added here -->
                        </div>
                    </div>
                `;
                $('#modules').append(moduleHtml);
            });

            // Remove module
            $(document).on('click', '.removeModule', function() {
                $(this).closest('.module').remove();
            });

            // Add content to module
            $(document).on('click', '.addContent', function() {
                const moduleId = $(this).data('module');
                const contentCount = $(`.contents[data-module="${moduleId}"] .content`).length + 1;
                const contentHtml = `
                    <div class="content">
                        <input type="text" name="modules[${moduleId}][contents][${contentCount}][title]" placeholder="Content Title (optional)">
                        <select name="modules[${moduleId}][contents][${contentCount}][type]" required>
                            <option value="">Select Type</option>
                            <option value="text">Text</option>
                            <option value="image">Image</option>
                            <option value="video">Video</option>
                            <option value="link">Link</option>
                        </select>
                        <textarea name="modules[${moduleId}][contents][${contentCount}][content]" placeholder="Content (e.g., text or URL)" required rows="2"></textarea>
                        <button type="button" class="removeContent">Remove Content</button>
                    </div>
                `;
                $(`.contents[data-module="${moduleId}"]`).append(contentHtml);
            });

            // Remove content
            $(document).on('click', '.removeContent', function() {
                $(this).closest('.content').remove();
            });

            // Frontend validation (basic)
            $('form').submit(function(e) {
                if (!$('input[name="title"]').val()) {
                    alert('Course title is required!');
                    e.preventDefault();
                }
                if ($('#modules .module').length === 0) {
                    alert('At least one module is required!');
                    e.preventDefault();
                }
            });

            // Add first module by default
            $('#addModule').click();
        });
    </script>
</body>
</html>