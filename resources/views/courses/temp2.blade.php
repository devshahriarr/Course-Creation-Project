<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Course</title>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/46.1.0/ckeditor5.umd.js"></script>
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
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); /* Shadow effect added */
        }
        .content {
            background: #d2d3d7;
            margin: 10px 0;
            padding: 10px 25px 10px 10px;
            border-radius: 4px;
            border-left: 4px solid #533483;
        }
        .editor {
            border: 1px solid #533483;
            border-radius: 4px;
            min-height: 100px;
            margin: 5px 0;
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
        /* Modal CSS */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: #ffffff;
            margin: 15% auto;
            padding: 20px;
            border-radius: 8px;
            width: 80%;
            max-width: 500px;
        }
        .file-input { margin: 5px 0; }
        #coursesTable {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        #coursesTable th, #coursesTable td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        #coursesTable th {
            background-color: #f2f2f2;
        }
        .course-image { max-width: 50px; height: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Create a New Course</h1>

        @if (session('success'))
            <p class="success">{{ session('success') }}</p>
        @endif

        <form id="courseForm" enctype="multipart/form-data">
            @csrf

            <!-- Course Fields -->
            <h2>Course Details</h2>
            <input type="text" name="title" placeholder="Course Title" value="{{ old('title') }}" required>
            @error('title') <span class="error">{{ $message }}</span> @enderror

            <div class="editor" id="descriptionEditor">{{ old('description') }}</div>
            <input type="hidden" name="description" id="descriptionHidden">
            @error('description') <span class="error">{{ $message }}</span> @enderror

            <div style="display: flex; align-items: center;">
                <select name="course_category_id" required style="flex: 1;">
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

            <input type="file" name="image" class="file-input" accept="image/*">
            @error('image') <span class="error">{{ $message }}</span> @enderror

            <input type="file" name="feature_image" class="file-input" accept="image/*">
            @error('feature_image') <span class="error">{{ $message }}</span> @enderror

            <input type="file" name="feature_video" class="file-input" accept="video/*">
            @error('feature_video') <span class="error">{{ $message }}</span> @enderror

            <input type="number" name="price" placeholder="Course Price" step="0.01" value="{{ old('price') }}" required min="0">
            @error('price') <span class="error">{{ $message }}</span> @enderror

            <!-- Modules Section -->
            <h2>Modules</h2>
            <button type="button" id="addModule">Add Module</button>
            <div id="modules">
                <!-- Dynamic modules added here -->
            </div>

            <button type="submit">Save Course</button>
        </form>

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

        <!-- Courses Table -->
        <h2>All Courses</h2>
        <table id="coursesTable">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Image</th>
                </tr>
            </thead>
            <tbody>
                <!-- Populated via JS -->
            </tbody>
        </table>
    </div>

    <script>
        let editors = {}; // Track CKEditors
        let moduleCount = 0;
        let descriptionEditor;

        $(document).ready(function() {
            // Init description editor
            ClassicEditor
                .create(document.querySelector('#descriptionEditor'))
                .then(editor => {
                    descriptionEditor = editor;
                })
                .catch(error => console.error(error));

            // Set hidden on submit
            $('form').on('submit', function() {
                if (descriptionEditor) {
                    $('#descriptionHidden').val(descriptionEditor.getData());
                }
                // Set content from editors
                $('.content').each(function() {
                    const mid = $(this).closest('.module').data('module');
                    const cid = $(this).index() + 1; // Approximate
                    const hiddenId = `hidden-content-${mid}-${cid}`;
                    const editorKey = `editor-${mid}-${cid}`;
                    if (editors[editorKey]) {
                        $(`#${hiddenId}`).val(editors[editorKey].getData());
                    }
                });
            });

            // Load initial table
            loadCoursesTable();

            // Category Modal
            $('#addCategory').click(function() {
                $('#categoryModal').show();
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
                            alert('Category created!');
                        }
                    },
                    error: function(xhr) {
                        alert('Error: ' + (xhr.responseJSON.error || 'Failed'));
                    }
                });
            });

            // Add Module
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
                        </div>
                    </div>
                `;
                $('#modules').append(moduleHtml);
            });

            // Remove Module
            $(document).on('click', '.removeModule', function() {
                $(this).closest('.module').remove();
                // Destroy editors if any
            });

            // Add Content
            $(document).on('click', '.addContent', function() {
                const moduleId = $(this).data('module');
                const contentCount = $(`.contents[data-module="${moduleId}"] .content`).length + 1;
                const contentHtml = `
                    <div class="content">
                        <input type="text" name="modules[${moduleId}][contents][${contentCount}][title]" placeholder="Content Title (optional)">
                        <select name="modules[${moduleId}][contents][${contentCount}][type]" class="content-type" data-module="${moduleId}" data-count="${contentCount}" required>
                            <option value="">Select Type</option>
                            <option value="text">Text</option>
                            <option value="image">Image</option>
                            <option value="video">Video</option>
                            <option value="link">Link</option>
                        </select>
                        <div class="content-fields">
                            <!-- Dynamic fields here -->
                        </div>
                        <input type="hidden" name="modules[${moduleId}][contents][${contentCount}][content]" id="hidden-content-${moduleId}-${contentCount}">
                        <button type="button" class="removeContent">Remove Content</button>
                    </div>
                `;
                $(`.contents[data-module="${moduleId}"]`).append(contentHtml);
            });

            // Dynamic Content Fields on Type Change
            $(document).on('change', '.content-type', function() {
                const type = $(this).val();
                const moduleId = $(this).data('module');
                const count = $(this).data('count');
                const fieldsDiv = $(this).siblings('.content-fields');
                const hiddenId = `#hidden-content-${moduleId}-${count}`;
                const editorId = `editor-${moduleId}-${count}`;
                fieldsDiv.empty();

                if (type === 'text') {
                    const editorDiv = `<div class="editor" id="${editorId}"></div>`;
                    fieldsDiv.html(editorDiv);
                    ClassicEditor
                        .create(document.querySelector(`#${editorId}`))
                        .then(editor => {
                            editors[editorId] = editor;
                        })
                        .catch(error => console.error(error));
                } else if (type === 'image') {
                    fieldsDiv.html(`<input type="file" name="modules[${moduleId}][contents][${count}][content_file]" accept="image/*" class="file-input">`);
                } else if (type === 'video') {
                    fieldsDiv.html(`<input type="file" name="modules[${moduleId}][contents][${count}][content_file]" accept="video/*" class="file-input">`);
                } else if (type === 'link') {
                    fieldsDiv.html(`<input type="url" placeholder="Enter URL" name="modules[${moduleId}][contents][${count}][content]" required>`);
                }
            });

            // Remove Content
            $(document).on('click', '.removeContent', function() {
                const content = $(this).closest('.content');
                const moduleId = content.closest('.module').data('module');
                const count = content.index() + 1;
                const editorId = `editor-${moduleId}-${count}`;
                if (editors[editorId]) {
                    editors[editorId].destroy();
                    delete editors[editorId];
                }
                content.remove();
            });

            // Frontend Validation
            $('#courseForm').submit(function(e) {
                if (!$('input[name="title"]').val() || !$('#descriptionHidden').val()) {
                    alert('Title and description required!');
                    e.preventDefault();
                    return false;
                }
                if ($('#modules .module').length === 0) {
                    alert('At least one module required!');
                    e.preventDefault();
                    return false;
                }
            });

            // AJAX Form Submit
            $('#courseForm').on('submit', function(e) {
                e.preventDefault();
                var formData = new FormData(this);
                $.ajax({
                    url: '{{ route("courses.store") }}',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            alert(response.success);
                            $('#courseForm')[0].reset();
                            if (descriptionEditor) descriptionEditor.setData('');
                            $('#modules').empty();
                            moduleCount = 0;
                            loadCoursesTable(); // Reload table
                        }
                    },
                    error: function(xhr) {
                        const errors = xhr.responseJSON.error || 'Validation failed';
                        alert('Error: ' + (typeof errors === 'object' ? JSON.stringify(errors) : errors));
                    }
                });
            });

            // Add first module
            $('#addModule').click();
        });

        function loadCoursesTable() {
            $.get('{{ route("courses.index") }}', function(data) {
                const tbody = $('#coursesTable tbody');
                tbody.empty();
                data.forEach(course => {
                    const row = `
                        <tr>
                            <td>${course.title}</td>
                            <td>${course.category ? course.category.category_name : 'N/A'}</td>
                            <td>$${course.price}</td>
                            <td><img src="${course.image ? '/storage/' + course.image : ''}" alt="Image" class="course-image" onerror="this.src='';"></td>
                        </tr>
                    `;
                    tbody.append(row);
                });
            });
        }
    </script>
</body>
</html>