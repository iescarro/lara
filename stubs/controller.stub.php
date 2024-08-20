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

    public function add()
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

    public function edit({{className}} {{variableName}})
    {
        return view('{{componentsName}}.edit', ['post' => {{variableName}}]);
    }

    private function update(Request $request)
    {
        {{className}}::validate($request);
        {{variableName}} = $request->only([{{parameters}}]);
        {{className}}::update({{variableName}});
        redirect()->route('{{componentsName}}.index')
            ->with('success', '{{className}} updated successfully.');
    }

    public function destroy({{className}} {{variableName}})
    {
        {{variableName}}->delete();
        redirect()->route('{{arrayName}}.index')
            ->with('success', '{{className}} deleted successfully.');
    }
}