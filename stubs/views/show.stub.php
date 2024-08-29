<h3>{{className}} details</h3>

{{properties}}

<a href="{{ route('{{componentsName}}.edit', {{variableName}}->id) }}">Edit</a>
<a href="{{ route('{{componentsName}}.index') }}">Back</a>

<form action="{{ routte('{{componentsName}}.destroy', {{variableName}}->id) }}" method="POST">
  @csrf
  @method('DELETE')
  <button class="btn btn-outline-danger">Delete</button>
</form>