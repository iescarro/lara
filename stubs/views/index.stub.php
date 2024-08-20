<h3>{{classesName}} List</h3>
<p>
  <a href="{{ route('{{tableName}}.add') }}">Add New {{className}}</a>
</p>
<table>
  <tr>
    {{columnHeaders}}
  </tr>
  @if (${{tableName}}->isEmpty())
  <p>No {{tableName}} available.</p>
  @else
  <tr>
    @foreach (${{tableName}} as ${{variableName}})
    {{columns}}
    @endforeach
  </tr>
  @endif
</table>