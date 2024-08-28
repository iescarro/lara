<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{{className}};

class {{controllerName}} extends Controller
{
    public function index()
    {
        {{arrayName}} = {{className}}::all();
        return view('{{componentsName}}.index', ['{{componentsName}}' => {{arrayName}}]);
    }

    public function create()
    {
        return view('{{componentsName}}.add');
    }

    public function store(Request $request)
    {
        {{className}}::validate($request);
        {{variableName}} = $request->only([{{parameters}}]);
        {{className}}::create({{variableName}});
        redirect()->route('{{componentsName}}.index')
            ->with('success', '{{className}} created successfully.');
    }

    public function show(string $id)
    {
        {{variableName}} = {{className}}::findOrFail($id);
        return view('{{arrayName}}.show', compact('{{componentName}}'));
    }

    public function edit({{className}} {{variableName}})
    {
        {{variableName}} = {{className}}::findOrFail($id);
        return view('{{componentsName}}.edit', ['{{componentName}}' => {{variableName}}]);
    }

    private function update(Request $request, string $id)
    {
        {{className}}::validate($request);
        {{variableName}} = $request->only([{{parameters}}]);
        {{variableName}}.save();
        redirect()->route('{{componentsName}}.index')
            ->with('success', '{{className}} updated successfully.');
    }

    public function destroy(string $id)
    {
        {{className}}::destroy($id);
        redirect()->route('{{arrayName}}.index')
            ->with('success', '{{className}} deleted successfully.');
    }
}