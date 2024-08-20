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
    // foreach ($fields as $field) {
    //   list($name, $type) = explode(':', $field);
    //   $output->writeln(sprintf('Field: %s, Type: %s', $name, $type));
    // }
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
    $this->generateMigration();
    $this->generateModel();
    $this->generateViews();
    $this->generateController();
    $this->updateRoute();
  }

  function generateMigration()
  {
    $migrationDirectory = 'database/migrations';
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
    $content = "<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('$tableName', function(Blueprint \$table) {
            \$table->id();
$columns
            \$table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('$tableName');
    }
};
";
    file_put_contents($filename, $content);
  }

  function generateModel()
  {
    $modelDirectory = 'app/Models';
    if (!is_dir($modelDirectory)) {
      mkdir($modelDirectory, 0777, true);
    }
    $modelName = ucwords($this->component);
    $filename = $modelDirectory . '/' . $modelName . '.php';
    $content = "<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class $modelName extends Model
{
    use HasFactory;
}
";
    file_put_contents($filename, $content);
  }

  function generateViews()
  {
    $currentDirectory = __DIR__;
    $stubDirectory = dirname($currentDirectory);

    $className = ucfirst($this->component);
    $tableName = lcfirst($this->component) . 's';
    $variableName = lcfirst($this->component);

    $viewsDirectory = 'resources/views/' . strtolower($this->component) . 's';
    if (!is_dir($viewsDirectory)) {
      mkdir($viewsDirectory, 0777, true);
    }

    $this->generateAddView($viewsDirectory, $stubDirectory, $className, $tableName);

    $this->generateEditView($viewsDirectory, $stubDirectory, $className, $tableName);

    // $editViewFileName = $viewsDirectory . '/edit.blade.php';
    // $editViewContent = file_get_contents($stubDirectory . '/stubs/views/edit.stub.php');
    // file_put_contents($editViewFileName, $editViewContent);

    $this->generateIndexView($viewsDirectory, $stubDirectory, $className, $tableName, $variableName);
  }

  function generateAddView($viewsDirectory, $stubDirectory, $className, $tableName)
  {
    $addViewFileName = $viewsDirectory . '/add.blade.php';
    $addViewContent = file_get_contents($stubDirectory . '/stubs/views/add.stub.php');
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
      ['{{className}}', '{{formGroups', '{{tableName'],
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
      ['{{className}}', '{{formGroups', '{{tableName'],
      [$className, $addFormGroups, $tableName],
      $addViewContent
    );
    file_put_contents($addViewFileName, $addViewContent);
  }

  function generateIndexView($viewsDirectory, $stubDirectory, $className, $tableName, $variableName)
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
      ['{{className}}', '{{tableName}}', '{{columnHeaders}}', '{{columns}}', '{{variableName}}'],
      [$className, $tableName, $columnHeaders, $columns, $variableName],
      $indexViewContent
    );
    file_put_contents($indexViewFileName, $indexViewContent);
  }

  function generateController()
  {
    $controllerDirectory = 'app/Http/Controllers';
    if (!is_dir($controllerDirectory)) {
      mkdir($controllerDirectory, 0777, true);
    }
    $controllerName = ucwords($this->component) . 'sController';
    $componentsName = lcfirst($this->component) . 's';
    $className = ucwords($this->component);
    $variableName = '$' . lcfirst($this->component);
    $arrayName = '$' . lcfirst($this->component) . 's';
    $filename = $controllerDirectory . '/' . $controllerName . '.php';
    $content = "<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\\$className;

class $controllerName extends Controller
{
    public function index()
    {
        $arrayName = $className::all();
        return view('$componentsName.index', ['$componentsName' => $arrayName]);
    }

    public function add()
    {
        return view('$componentsName.add');
    }

    public function store(Request \$request)
    {
        $className::create(\$request->all());
        redirect()->route('$componentsName.index')
            ->with('success', '$className created successfully.');
    }

    public function edit($className $variableName)
    {
        return view('$componentsName.edit', ['post' => $variableName]);
    }

    private function update(Request \$request)
    {
        $className::update(\$request->all());
        redirect()->route('$componentsName.index')
            ->with('success', '$className updated successfully.');
    }

    public function destroy($className $variableName)
    {
        {$variableName}->delete();
        redirect()->route('$arrayName.index')
            ->with('success', '$className deleted successfully.');
    }
}";
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
    $content = "
Route::get('/$arrayName', [$controllerName::class, 'index'])->name('$arrayName.index');
Route::get('/$arrayName/add', [$controllerName::class, 'add'])->name('$arrayName.add');
Route::post('/$arrayName/store', [$controllerName::class, 'store'])->name('$arrayName.store');
Route::get('/$arrayName/edit', [$controllerName::class, 'edit'])->name('$arrayName.edit');
Route::put('/$arrayName/update', [$controllerName::class, 'update'])->name('$arrayName.update');
Route::delete('/$arrayName/{$variableName}', [$controllerName::class, 'destroy'])->name('$arrayName.destroy');";
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
