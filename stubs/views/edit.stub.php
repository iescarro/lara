<h1>Edit {{variableName}}</h1>

@if ($errors->any())
<div class="alert alert-danger">
  <ul>
    @foreach ($errors->all() as $error)
    <li>{{ $error }}</li>
    @endforeach
  </ul>
</div>
@endif

<form action="{{ route('{{tableName}}.update', '{{variableName}}->id') }}" method="POST">
  @csrf
  @method('PUT')
  {{formGroups}}

  <button type="submit" class="btn btn-outline-primary">Update {{variableName}}</button>
</form>