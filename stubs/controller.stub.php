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
        return view('{{componentsName}}.create');
    }

    public function store(Request $request)
    {
        {{className}}::validate($request);
        {{variableName}} = new {{className}}();
{{classAssignments}}
        {{variableName}}->save();
        return redirect()->route('{{componentsName}}.index')
            ->with('success', '{{className}} created successfully.');
    }

    public function show(string $id)
    {
        {{variableName}} = {{className}}::findOrFail($id);
        return view('{{arrayName}}.show', ['{{componentName}}' => {{variableName}}]);
    }

    public function edit(string $id)
    {
        {{variableName}} = {{className}}::findOrFail($id);
        return view('{{componentsName}}.edit', ['{{componentName}}' => {{variableName}}]);
    }

    public function update(Request $request, string $id)
    {
        {{className}}::validate($request);
        {{variableName}} = {{className}}::findOrFail($id);
{{classAssignments}}
        {{variableName}}->save();
        return redirect()->route('{{componentsName}}.index')
            ->with('success', '{{className}} updated successfully.');
    }

    public function destroy(string $id)
    {
        {{className}}::destroy($id);
        return redirect()->route('{{componentsName}}.index')
            ->with('success', '{{className}} deleted successfully.');
    }
}
