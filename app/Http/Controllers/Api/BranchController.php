<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Resources\BranchResource;

class BranchController extends Controller
{
    // GET /api/branches
    public function index()
    {
        $branches = Branch::all();

        return response()->json([
            'status' => true,
            'data' => $branches
        ], 200);
    }

    // GET /api/branches/{id}
    public function show($id)
    {
        $branch = Branch::find($id);

        if (!$branch) {
            return response()->json([
                'status' => false,
                'message' => 'Branch not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $branch
        ], 200);
    }

    // POST /api/branches
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:branches,code',
            'address' => 'nullable|string',
        ]);

        $branch = Branch::create($validated);

        return response()->json([
            'status' => true,
            'data' => $branch
        ], 201);
    }

    // PUT /api/branches/{id}
    public function update(Request $request, $id)
    {
        $branch = Branch::find($id);

        if (!$branch) {
            return response()->json([
                'status' => false,
                'message' => 'Branch not found'
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'code' => [
                'sometimes',
                'required',
                'string',
                Rule::unique('branches', 'code')->ignore($branch->id)
            ],
            'address' => 'nullable|string',
        ]);

        $branch->update($validated);

        return response()->json([
            'status' => true,
            'data' => $branch
        ], 200);
    }

    // DELETE /api/branches/{id}
    public function destroy($id)
    {
        $branch = Branch::find($id);

        if (!$branch) {
            return response()->json([
                'status' => false,
                'message' => 'Branch not found'
            ], 404);
        }

        $branch->delete();

        return response()->json([
            'status' => true,
            'message' => 'Branch deleted successfully'
        ], 200);
    }
}
