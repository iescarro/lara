<?php
namespace Console;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateCommand extends Command {

  public function __construct() {
    parent::__construct();
  }

  public function configure() {
    $this->setName('generate:scaffold')
      ->addArgument('component', InputArgument::REQUIRED, '')
      ->addArgument('fields', InputArgument::IS_ARRAY, '');
  }

  public function execute(InputInterface $input, OutputInterface $output) {
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

class Generator {

  function __construct($component, $fields) {
    $this->component = $component;
    $this->fields = $fields;
  }

  function scaffold() {
    $this->generateMigration();
    $this->generateModel();
    $this->generateViews();
    $this->generateController();
  }

  function generateMigration() {
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
        Schema::create('', function(Blueprint \$table) {
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

  function generateModel() {
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

  function generateViews() {
    $viewsDirectory = 'resources/views/' . strtolower($this->component) . 's';
    if (!is_dir($viewsDirectory)) {
      mkdir($viewsDirectory, 0777, true);
    }
    $addViewFileName = $viewsDirectory . '/add.blade.php';
    $addViewContent = '';
    file_put_contents($addViewFileName, $addViewContent);

    $editViewFileName = $viewsDirectory . '/edit.blade.php';
    $editViewContent = '';
    file_put_contents($editViewFileName, $editViewContent);

    $indexViewFileName = $viewsDirectory . '/index.blade.php';
    $indexViewContent = '';
    file_put_contents($indexViewFileName, $indexViewContent);
  }

  function generateController() {
    $controllerDirectory = 'app/Http/Controllers';
    if (!is_dir($controllerDirectory)) {
      mkdir($controllerDirectory, 0777, true);
    }
    $controllerName = ucwords($this->component) . 'sController';
    $filename = $controllerDirectory . '/' . $controllerName . '.php';
    $content = "<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class $controllerName extends Controller
{
    //

    public function index() {

    }

    public function add() {

    }

    public function edit() {

    }

    public function delete() {
        
    }
}";
    file_put_contents($filename, $content);
  }

  function updateRoute() {
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

  function generateTests() {
    // TODO:
  }
}
