<h3>{{classesName}}</h3>
<p>
  <a href="{{ route('{{tableName}}.add') }}" class="btn btn-outline-primary">Create {{componentName}}</a>
</p>
@if ({{variableName}}->isEmpty())
<p>No {{tableName}} available.</p>
@else
<table class="table table-hover">
  <tr>
{{columnHeaders}}
  </tr>
  <tr>
    @foreach (${{tableName}} as {{variableName}})
{{columns}}
    @endforeach
  </tr>
</table>
@endif
