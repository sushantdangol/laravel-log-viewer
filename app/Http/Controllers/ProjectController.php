<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProjectController extends Controller
{
    /**
     * Store a newly created project
     */
    public function store(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'project_name' => 'required|string|max:255|unique:tbl_projects,project_name',
            'project_file_path' => 'required|string|max:500',
        ], [
            'project_name.required' => 'Project name is required.',
            'project_name.unique' => 'A project with this name already exists.',
            'project_file_path.required' => 'Project URL is required.',
        ]);

        if ($validator->fails()) {
            return redirect()->back();
        }

        try {
            Project::create([
                'project_name' => $request->project_name,
                'project_file_path' => $request->project_file_path,
            ]);

            return redirect()->back();

        } catch (\Exception $e) {
            return redirect()->back();
        }
    }
}
