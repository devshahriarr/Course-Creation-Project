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
            margin: 0;
        }
        .menubar {
            background: #28a745;
            padding: 10px 20px;
            margin-bottom: 20px;
            text-align: center; /* Center alignment for menubar */
        }
        .menubar a {
            color: #fff;
            text-decoration: none;
            margin: 0 20px;
            font-weight: bold;
        }
        .menubar a:hover {
            color: #218b39;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #ffffff;
            padding: 20px 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1); /* Subtle shadow for better alignment */
        }
        h1, h2, h3 {
            text-align: center;
            margin-bottom: 20px;
        }
        input, textarea, select {
            width: 100%;
            padding: 10px; /* Increased padding for better alignment */
            margin: 10px 0; /* Consistent margin */
            background: #ffffff;
            color: #262424;
            border: 1px solid #533483;
            border-radius: 4px;
            box-sizing: border-box; /* Prevents overflow */
        }
        .category-section {
            display: flex;
            align-items: center;
            gap: 10px; /* Better spacing between select and button */
        }
        .category-section select {
            flex: 1;
            margin: 0; /* Remove default margin */
        }
        .module {
            background: #ffffff;
            margin: 15px 0; /* Increased margin for nested structure */
            padding: 20px; /* More padding for alignment */
            border-radius: 6px;
            border-left: 4px solid #29b74a;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1); /* Light shadow for depth */
        }
        .content {
            background: #d2d3d7;
            margin: 10px 0;
            padding: 15px; /* Consistent padding */
            border-radius: 4px;
            border-left: 4px solid #533483;
            position: relative; /* For better nesting */
        }
        button {
            background: #28a745;
            color: #fff;
            border: none;
            padding: 10px 15px; /* Consistent padding */
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
            display: block; /* Ensure block display for alignment below fields */
            margin-top: 5px;
        }
        .success {
            color: #28a745;
            text-align: center;
            padding: 10px;
            background: #d4edda;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        /* Modal CSS - Kept as is, but aligned */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }
        .modal-content {
            background-color: #ffffff;
            margin: 15% auto;
            padding: 20px;
            border-radius: 8px;
            width: 80%;
            max-width: 500px;
            color: #131313;
        }
        .modal-content input, .modal-content textarea {
            background: #ffffff;
            color: #131313;
            border: 1px solid #533483;
        }
        .modal-content button {
            background: #28a745;
            color: #ffffff;
        }
        .modal-content button:hover {
            background: #218b39;
        }
    </style>
</head>
<body>
    <div class="menubar">
        <a href="{{ route('courses.create') }}">Course Add</a>
        <a href="{{ route('courses.index') }}">Course List</a>
    </div>
    <div class="container">
        <h1>Create a New Course</h1>

        @if (session('success'))
            <p class="success">{{ session('success') }}</p>
        @endif

        @if (session('error'))
            <p class="error">{{ session('error') }}</p>
        @endif

        <form method="POST" action="{{ route('courses.store') }}" id="courseForm">
            @csrf

            <!-- Course Fields -->
            <h2>Course Details</h2>
            <input type="text" name="title" placeholder="Course Title" value="{{ old('title') }}" required>
            @error('title') <span class="error">{{ $message }}</span> @enderror
            <input type="text" name="price" placeholder="Course Price" value="{{ old('price') }}" required>
            @error('price') <span class="error">{{ $message }}</span> @enderror

            <textarea name="description" placeholder="Description" rows="3">{{ old('description') }}</textarea>
            @error('description') <span class="error">{{ $message }}</span> @enderror

            <div class="category-section">
                <select name="course_category_id" required>
                    <option value="">Select Category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('course_category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->category_name }}
                        </option>
                    @endforeach
                </select>
                <button type="button" id="addCategory">+ Add New</button>
            </div>
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

    <!-- Category Modal -->
    <div id="categoryModal" class="modal">
        <div class="modal-content">
            <h3>Add New Category</h3>
            <form id="categoryForm">
                @csrf
                <input type="text" name="category_name" placeholder="Category Name" required>
                <textarea name="description" placeholder="Description" rows="2"></textarea>
                <input type="file" name="image" accept="image/*">
                <button type="submit">Create</button>
                <button type="button" id="closeModal">Close</button>
            </form>
        </div>
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
                        <span class="error-message"></span>
                    </div>
                `;
                $(`.contents[data-module="${moduleId}"]`).append(contentHtml);
            });

            // Remove content
            $(document).on('click', '.removeContent', function() {
                $(this).closest('.content').remove();
            });

            // Frontend Validation (scoped to course form) - Basic for now, can enhance later
            $('#courseForm').submit(function(e) {
                const title = $('input[name="title"]').val().trim(); // Trim spaces
                if (!title) {
                    alert('Course title is required!');
                    e.preventDefault();
                    return false;
                }
                if ($('#modules .module').length === 0) {
                    alert('At least one module is required!');
                    e.preventDefault();
                    return false;
                }
            });

            // Add first module by default
            $('#addModule').click();

            // Category Modal
            $('#addCategory').click(function() {
                $('#categoryModal').show();
                $('#categoryForm')[0].reset(); // Reset on open
            });

            $('#closeModal').click(function() {
                $('#categoryModal').hide();
                $('#categoryForm')[0].reset();
            });

            $('#categoryForm').submit(function(e) {
                e.preventDefault();
                var formData = new FormData(this);
                $.ajax({
                    url: '{{ route("categories.store") }}',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            const newOption = `<option value="${response.category.id}">${response.category.category_name}</option>`;
                            $('select[name="course_category_id"]').append(newOption);
                            $('select[name="course_category_id"]').val(response.category.id);
                            $('#categoryModal').hide();
                            $('#categoryForm')[0].reset();
                            // alert('Category created!');
                            window.location.href = '{{ route("courses.create") }}?success=Category created successfully!';
                        }
                    },
                    error: function(xhr) {
                        alert('Error: ' + (xhr.responseJSON.error || 'Failed'));
                    }
                });
            });
        });
    </script>
</body>
</html>