<?php

namespace Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateCommand extends Command
{
  protected static $defaultName = 'generate:scaffold'; // Use static property for default command name

  public function __construct()
  {
    parent::__construct();
  }

  public function configure(): void
  {
    $this
      ->setName('generate:scaffold')
      ->setDescription('')
      ->addArgument('component', InputArgument::REQUIRED, '')
      ->addArgument('fields', InputArgument::IS_ARRAY, '');
  }

  public function execute(InputInterface $input, OutputInterface $output): int
  {
    $component = $input->getArgument('component');
    $fields = $input->getArgument('fields');

    $generator = new Generator($component, $fields);
    $generator->scaffold();
    return Command::SUCCESS;
  }
}

class Generator
{
  private $component;
  private $fields;

  function __construct($component, $fields)
  {
    $this->component = $component;
    $this->fields = $fields;
  }

  function scaffold()
  {
    $currentDirectory = __DIR__;
    $stubDirectory = dirname($currentDirectory);

    $this->generateMigration($stubDirectory);
    $this->generateModel($stubDirectory);
    $this->generateViews($stubDirectory);
    $this->generateController($stubDirectory);
    $this->updateRoute();
  }

  function generateMigration($stubDirectory)
  {
    $migrationDirectory = 'database/migrations';
    $content = file_get_contents($stubDirectory . '/stubs/migration.stub.php');
    if (!is_dir($migrationDirectory)) {
      mkdir($migrationDirectory, 0777, true);
    }
    $migrationName = date('Y_m_d_His', time()) . '_create_' . strtolower($this->component) . 's';
    $tableName = strtolower($this->component) . 's';
    $filename = $migrationDirectory . '/' . $migrationName . '.php';
    $columns = '';
    foreach ($this->fields as $field) {
      list($name, $type) = explode(':', $field);
      $columns .= '            $table->' . $type . "('" . $name . "');\n";
    }
    $content = str_replace(
      ['{{tableName}}', '{{columns}}'],
      [$tableName, $columns],
      $content
    );
    file_put_contents($filename, $content);
  }

  function generateModel($stubDirectory)
  {
    $modelDirectory = 'app/Models';
    $content = file_get_contents($stubDirectory . '/stubs/model.stub.php');
    if (!is_dir($modelDirectory)) {
      mkdir($modelDirectory, 0777, true);
    }
    $className = ucwords($this->component);
    $filename = $modelDirectory . '/' . $className . '.php';
    $properties = '';
    foreach ($this->fields as $field) {
      list($name, $type) = explode(':', $field);
      $properties .= "            '" . $name . "' => 'required',\n";
    }
    $content = str_replace(
      ['{{className}}', '{{properties}}'],
      [$className, $properties],
      $content
    );
    file_put_contents($filename, $content);
  }

  function generateViews($stubDirectory)
  {
    $componentName = lcfirst($this->component);
    $className = ucfirst($this->component);
    $classesName = ucfirst($this->component) . 's';
    $tableName = lcfirst($this->component) . 's';
    $variableName = '$' . lcfirst($this->component);

    $viewsDirectory = 'resources/views/' . strtolower($this->component) . 's';
    if (!is_dir($viewsDirectory)) {
      mkdir($viewsDirectory, 0777, true);
    }

    $this->generateCreateView($viewsDirectory, $stubDirectory, $className, $tableName, $componentName);
    $this->generateEditView($viewsDirectory, $stubDirectory, $className, $tableName, $componentName);
    $this->generateIndexView($viewsDirectory, $stubDirectory, $className, $tableName, $variableName, $classesName, $componentName);
    $this->generateShowView($viewsDirectory, $stubDirectory, $classesName, $variableName);
  }

  function generateCreateView($viewsDirectory, $stubDirectory, $className, $tableName, $componentName)
  {
    $addViewFileName = $viewsDirectory . '/create.blade.php';
    $addViewContent = file_get_contents($stubDirectory . '/stubs/views/create.stub.php');
    $formGroups = '';
    foreach ($this->fields as $field) {
      list($name, $type) = explode(':', $field);
      $fieldName = ucwords($name);
      $formGroups .= "
  <div class=\"form-group\">
    <label for=\"$name\">$fieldName</label>
    <input type=\"text\" id=\"$name\" name=\"$name\" class=\"form-control\" value=\"{{ old('$name') }}\">
  </div>";
    }
    $addViewContent = str_replace(
      ['{{className}}', '{{formGroups}}', '{{tableName}}', '{{componentName}}'],
      [$className, $formGroups, $tableName, $componentName],
      $addViewContent
    );
    file_put_contents($addViewFileName, $addViewContent);
  }

  function generateEditView($viewsDirectory, $stubDirectory, $className, $tableName, $componentName)
  {
    $addViewFileName = $viewsDirectory . '/edit.blade.php';
    $addViewContent = file_get_contents($stubDirectory . '/stubs/views/edit.stub.php');
    $formGroups = '';
    $variableName = '$' . $componentName;
    foreach ($this->fields as $field) {
      list($name, $type) = explode(':', $field);
      $fieldName = ucwords($name);
      $formGroups .= "
  <div class=\"form-group\">
    <label for=\"$name\">$fieldName</label>
    <input type=\"text\" id=\"$name\" name=\"$name\" class=\"form-control\" value=\"{{ {$variableName}->{$name} }}\">
  </div>";
    }
    $addViewContent = str_replace(
      ['{{className}}', '{{formGroups}}', '{{tableName}}', '{{componentName}}', '{{variableName}}'],
      [$className, $formGroups, $tableName, $componentName, $variableName],
      $addViewContent
    );
    file_put_contents($addViewFileName, $addViewContent);
  }

  function generateIndexView($viewsDirectory, $stubDirectory, $className, $tableName, $variableName, $classesName, $componentName)
  {
    $indexViewFileName = $viewsDirectory . '/index.blade.php';
    $indexViewContent = file_get_contents($stubDirectory . '/stubs/views/index.stub.php');
    $columnHeaders = '';
    $columns = '';
    foreach ($this->fields as $field) {
      list($name, $type) = explode(':', $field);
      $headerName = ucfirst($name);
      $columnHeaders .= "    <th>$headerName</th>\n";
      $columns .= "    <td>{{ {$variableName}->$name }}</td>\n";
    }
    $columnHeaders .= '    <th></th>';
    $columns .= "    <td>
      <a href=\"{{ route('{$tableName}.edit', {$variableName}->id) }}\">Edit</a>
    </td>\n";
    $indexViewContent = str_replace(
      ['{{className}}', '{{tableName}}', '{{columnHeaders}}', '{{columns}}', '{{variableName}}', '{{classesName}}', '{{componentName}}'],
      [$className, $tableName, $columnHeaders, $columns, $variableName, $classesName, $componentName],
      $indexViewContent
    );
    file_put_contents($indexViewFileName, $indexViewContent);
  }

  function generateShowView($viewsDirectory, $stubDirectory, $className, $variableName)
  {
    $showViewFileName = $viewsDirectory . '/show.blade.php';
    $showViewContent = file_get_contents($stubDirectory . '/stubs/views/show.stub.php');
    $componentsName = lcfirst($this->component) . 's';
    $properties = '';
    foreach ($this->fields as $field) {
      list($name, $type) = explode(':', $field);
      $label = ucfirst($name);
      $properties .= "<p><strong>$label</strong>:
    {{ $" . $variableName . "->" . $name . " }}
</p>\n";
    }
    $showViewContent = str_replace(
      ['{{className}}', '{{variableName}}', '{{componentsName}}', '{{properties}}'],
      [$className, $variableName, $componentsName, $properties],
      $showViewContent
    );
    file_put_contents($showViewFileName, $showViewContent);
  }

  function generateController($stubDirectory)
  {
    $controllerDirectory = 'app/Http/Controllers';
    $content = file_get_contents($stubDirectory . '/stubs/controller.stub.php');
    if (!is_dir($controllerDirectory)) {
      mkdir($controllerDirectory, 0777, true);
    }
    $controllerName = ucwords($this->component) . 'sController';
    $componentName = lcfirst($this->component);
    $componentsName = lcfirst($this->component) . 's';
    $className = ucwords($this->component);
    $variableName = '$' . lcfirst($this->component);
    $arrayName = '$' . lcfirst($this->component) . 's';
    $filename = $controllerDirectory . '/' . $controllerName . '.php';
    $parameters = '';
    $classAssignments = '';
    foreach ($this->fields as $field) {
      list($name, $type) = explode(':', $field);
      $parameters .= '"' . $name . '", ';
      $classAssignments .= '        ' . $variableName . '->' . $name . " = \$request->input('" . $name . "');\n";
    }
    $parameters = trim(trim($parameters), ",");
    $content = str_replace(
      ['{{className}}', '{{controllerName}}', '{{arrayName}}', '{{componentsName}}', '{{variableName}}', '{{parameters}}', '{{componentName}}', '{{classAssignments}}'],
      [$className, $controllerName, $arrayName, $componentsName, $variableName, $parameters, $componentName, $classAssignments],
      $content
    );
    file_put_contents($filename, $content);
  }

  function updateRoute()
  {
    $routesDirectory = 'routes';
    if (!is_dir($routesDirectory)) {
      mkdir($routesDirectory, 0777, true);
    }
    $routeFileName = $routesDirectory . '/web.php';
    $controllerName = ucwords($this->component) . 'sController';
    $variableName = strtolower($this->component);
    $arrayName = strtolower($this->component) . 's';
    //     $content = "
    // Route::get('/$arrayName', [$controllerName::class, 'index'])->name('$arrayName.index');
    // Route::get('/$arrayName/add', [$controllerName::class, 'add'])->name('$arrayName.add');
    // Route::post('/$arrayName/store', [$controllerName::class, 'store'])->name('$arrayName.store');
    // Route::get('/$arrayName/edit', [$controllerName::class, 'edit'])->name('$arrayName.edit');
    // Route::put('/$arrayName/update', [$controllerName::class, 'update'])->name('$arrayName.update');
    // Route::delete('/$arrayName/{$variableName}', [$controllerName::class, 'destroy'])->name('$arrayName.destroy');";
    $content = "
use App\Http\Controllers\\$controllerName;
Route::resource('/$arrayName', $controllerName::class);";
    $routeFile = fopen($routeFileName, 'a');
    if ($routeFile) {
      fwrite($routeFile, $content);
      fclose($routeFile);
    }
  }

  function generateTests()
  {
    // TODO:
  }
}
