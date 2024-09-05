<h3>{{classesName}}</h3>
<p>
  <a href="{{ route('{{tableName}}.add') }}" class="btn btn-outline-primary">Create {{componentName}}</a>
</p>
@if (${{tableName}}->isEmpty())
<p>No {{tableName}} available.</p>
@else
<table class="table table-hover">
  <tr>
{{columnHeaders}}
  </tr>
  @foreach (${{tableName}} as {{variableName}})
  <tr>    
{{columns}}
  </tr>
  @endforeach
</table>
@endif
