<h1>Add {{className}}</h1>

@if ($errors->any())
<div class="alert alert-danger">
  <ul>
    @foreach ($errors->all() as $error)
    <li>{{ $error }}</li>
    @endforeach
  </ul>
</div>
@endif

<form action="{{ route('{{tableName}}.store') }}" method="POST">
  @csrf
  {{formGroups}}

  <button type="submit" class="btn btn-primary">Add {{className}}</button>
</form>