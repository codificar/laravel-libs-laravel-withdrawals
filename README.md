# laravel-generic-lib
A generic library for laravel. Just fork and modify it.

## Getting Started


- In root of your Laravel app in the composer.json add this code to clone the project:

```

"repositories": [
    {
        "type": "git",
        "url": "git@git.codificar.com.br:react-components/laravel-generic-lib.git"
    }
],

// ...

"require": {
    // ADD this
    "codificar/generic": "dev-master",
},

```

- Add 
```

"autoload": {
        "classmap": [
            "database/seeds"
        ],
        "psr-4": {
            // Add your Lib here
            "Codificar\\Generic\\": "vendor/codificar/generic/src",
            "App\\": "app/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            // Add your Lib here
            "Codificar\\Generic\\": "vendor/codificar/generic/src",
            "Tests\\": "tests/"
        }
    },
```
- Dump the composer autoloader

```
composer dump-autoload -o
```

- Next, we need to add our new Service Provider in our `config/app.php` inside the `providers` array:

```
'providers' => [
         ...,
            // The new package class
            Codificar\Generic\GenericServiceProvider::class,
        ],
```
- Migrate the database tables

```
php artisan migrate
```

And finally, start the application by running:

```
php artisan serve
```

Visit http://localhost:8000/contact in your browser to view the demo.