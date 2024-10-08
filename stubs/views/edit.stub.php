<h3>Edit {{componentName}}</h3>
@if ($errors->any())
<div class="alert alert-danger">
  <ul>
    @foreach ($errors->all() as $error)
    <li>{{ $error }}</li>
    @endforeach
  </ul>
</div>
@endif

<form action="{{ route('{{tableName}}.update', {{variableName}}->id) }}" method="POST">
  @csrf
  @method('PUT')
  {{formGroups}}

  <button type="submit" class="btn btn-outline-primary">Update {{componentName}}</button>
</form>

<form action="{{ route('{{tableName}}.destroy', {{variableName}}->id) }}" method="POST">
  @csrf
  @method('DELETE')
  <button type="submit" class="btn btn-danger">Delete</button>
</form>
