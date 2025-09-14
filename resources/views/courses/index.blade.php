<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course List</title>
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
            text-align: center; /* Center alignment */
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
            max-width: 1200px; /* Wider for table */
            margin: 0 auto;
            background: #ffffff;
            padding: 20px 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1); /* Shadow for table */
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px; /* Better padding for alignment */
            text-align: left;
        }
        th {
            background-color: #28a745;
            color: #fff;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2; /* Alternating rows for better readability */
        }
        tr:hover {
            background-color: #e8f5e8; /* Hover effect */
        }
        .error {
            color: #e94560;
            font-size: 0.9em;
        }
        .success {
            color: #28a745;
            text-align: center;
            padding: 10px;
            background: #d4edda;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        @media (max-width: 768px) {
            table, th, td {
                font-size: 14px; /* Responsive for mobile */
            }
        }
    </style>
</head>
<body>
    <div class="menubar">
        <a href="{{ route('courses.create') }}">Course Add</a>
        <a href="{{ route('courses.index') }}">Course List</a>
    </div>
    <div class="container">
        <h1>Course List</h1>

        @if (session('success'))
            <p class="success">{{ session('success') }}</p>
        @endif
        @if (session('error'))
            <p class="error">{{ session('error') }}</p>
        @endif

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Description (Snippet)</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($courses as $course)
                    <tr>
                        {{-- @dd($course); --}}
                        <td>{{ $course->id }}</td>
                        <td>{{ $course->title }}</td>
                        <td>{{ $course->course_category ? $course->course_category->category_name : 'N/A' }}</td>
                        <td>{{ substr($course->description ?? '', 0, 50) }}{{ strlen($course->description ?? '') > 50 ? '...' : '' }}</td>
                        <td>{{ $course->created_at->format('Y-m-d H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center;">No courses found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <script>
        $(document).ready(function() {
            // Optional: Add search or pagination later if needed
        });
    </script>
</body>
</html>