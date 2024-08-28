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
    $className = ucfirst($this->component);
    $classesName = ucfirst($this->component) . 's';
    $tableName = lcfirst($this->component) . 's';
    $variableName = lcfirst($this->component);

    $viewsDirectory = 'resources/views/' . strtolower($this->component) . 's';
    if (!is_dir($viewsDirectory)) {
      mkdir($viewsDirectory, 0777, true);
    }

    $this->generateCreateView($viewsDirectory, $stubDirectory, $className, $tableName);
    $this->generateEditView($viewsDirectory, $stubDirectory, $className, $tableName);
    $this->generateIndexView($viewsDirectory, $stubDirectory, $className, $tableName, $variableName, $classesName);
  }

  function generateCreateView($viewsDirectory, $stubDirectory, $className, $tableName)
  {
    $addViewFileName = $viewsDirectory . '/create.blade.php';
    $addViewContent = file_get_contents($stubDirectory . '/stubs/views/create.stub.php');
    $addFormGroups = '';
    foreach ($this->fields as $field) {
      list($name, $type) = explode(':', $field);
      $fieldName = ucwords($name);
      $addFormGroups .= "<div class=\"form-group\">
    <label for=\"$name\">$fieldName</label>
    <input type=\"text\" id=\"$name\" name=\"$name\" class=\"form-control\" value=\"{{ old('$name') }}\">
  </div>";
    }
    $addViewContent = str_replace(
      ['{{className}}', '{{formGroups}}', '{{tableName}}'],
      [$className, $addFormGroups, $tableName],
      $addViewContent
    );
    file_put_contents($addViewFileName, $addViewContent);
  }

  function generateEditView($viewsDirectory, $stubDirectory, $className, $tableName)
  {
    $addViewFileName = $viewsDirectory . '/edit.blade.php';
    $addViewContent = file_get_contents($stubDirectory . '/stubs/views/edit.stub.php');
    $addFormGroups = '';
    foreach ($this->fields as $field) {
      list($name, $type) = explode(':', $field);
      $fieldName = ucwords($name);
      $addFormGroups .= "<div class=\"form-group\">
    <label for=\"$name\">$fieldName</label>
    <input type=\"text\" id=\"$name\" name=\"$name\" class=\"form-control\" value=\"{{ old('$name') }}\">
  </div>";
    }
    $addViewContent = str_replace(
      ['{{className}}', '{{formGroups}}', '{{tableName}}'],
      [$className, $addFormGroups, $tableName],
      $addViewContent
    );
    file_put_contents($addViewFileName, $addViewContent);
  }

  function generateIndexView($viewsDirectory, $stubDirectory, $className, $tableName, $variableName, $classesName)
  {
    $indexViewFileName = $viewsDirectory . '/index.blade.php';
    $indexViewContent = file_get_contents($stubDirectory . '/stubs/views/index.stub.php');
    $columnHeaders = '';
    $columns = '';
    foreach ($this->fields as $field) {
      list($name, $type) = explode(':', $field);
      $headerName = ucfirst($name);
      $columnHeaders .= "    <th>$headerName</th>\n";
      $columns .= "    <td>{{ \${$variableName}->$name }}</td>\n";
    }
    $indexViewContent = str_replace(
      ['{{className}}', '{{tableName}}', '{{columnHeaders}}', '{{columns}}', '{{variableName}}', '{{classesName}}'],
      [$className, $tableName, $columnHeaders, $columns, $variableName, $classesName],
      $indexViewContent
    );
    file_put_contents($indexViewFileName, $indexViewContent);
  }

  function generateController($stubDirectory)
  {
    $controllerDirectory = 'app/Http/Controllers';
    $content = file_get_contents($stubDirectory . '/stubs/controller.stub.php');
    if (!is_dir($controllerDirectory)) {
      mkdir($controllerDirectory, 0777, true);
    }
    $controllerName = ucwords($this->component) . 'sController';
    $componentName = $this->component;
    $componentsName = lcfirst($this->component) . 's';
    $className = ucwords($this->component);
    $variableName = '$' . lcfirst($this->component);
    $arrayName = '$' . lcfirst($this->component) . 's';
    $filename = $controllerDirectory . '/' . $controllerName . '.php';
    $parameters = '';
    foreach ($this->fields as $field) {
      list($name, $type) = explode(':', $field);
      $parameters .= '"' . $name . '", ';
    }
    $parameters = trim(trim($parameters), ",");
    $content = str_replace(
      ['{{className}}', '{{controllerName}}', '{{arrayName}}', '{{componentsName}}', '{{variableName}}', '{{parameters}}', '{{componentName}}'],
      [$className, $controllerName, $arrayName, $componentsName, $variableName, $parameters, $componentName],
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
