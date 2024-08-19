# Lara - Laravel Scaffolding Tool

Lara is a PHP console application designed to simplify the creation of scaffolding components in Laravel, inspired by the Rails generate command. Currently, it supports generating scaffold components.

## Installation

To install Lara, you can use Composer. Add the package to your `composer.json` file or run the following command:

```sh
composer require iescarro/lara
```

## Usage

Lara can be used to generate scaffold components in your Laravel application. The basic syntax for generating a scaffold is:

```sh```
php vendor/iescarro/lara/lara generate scaffold <ModelName> <field1:type> <field2:type> ...
```

## Example

To generate a scaffold for a Post model with title as a string and content as text, you would run the following command:

```sh
php vendor/iescarro/lara/lara generate scaffold Post title:string content:text
```

This command will generate the necessary files and code for the Post model, including:

- Migration
- Model
- Controller
- Request
- Routes
- Views

### Generated Components

- Migration: A new migration file for the model with the specified fields.
- Model: A new Eloquent model file.
- Controller: A new resource controller for the model.
- Request: A new form request validation class.
- Routes: Updates to the web.php file with the necessary routes.
- Views: Basic views for creating, updating, and listing the model.

## Contributing

We welcome contributions to improve Lara. If you would like to contribute, please fork the repository and submit a pull request.

## License

This project is licensed under the MIT License. See the LICENSE file for details.

## Support

For any questions or support, please open an issue on GitHub or contact us.