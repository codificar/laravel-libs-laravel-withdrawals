# laravel-withdrawals

A withdrawals library for laravel.
Uma bibliotéca de saques feita em laravel

## Prerequisites

- These middwares are needed:
- If your project does not have some of these middleware, it is necessary to add them.
```
auth.admin
auth.provider
auth.provider_api:api
```
- The following tables are required. The columns are the same as in the UBER CLONE project:
```
Provider
Settings
Finance
Ledger
Bank
LedgerBankAccount
```

## Getting Started


Add in composer.json:

```php
"repositories": [
    {
        "type": "vcs",
        "url": "https://libs:ofImhksJ@git.codificar.com.br/laravel-libs/laravel-withdrawals.git"
    }
]
```

```php
require:{
        "codificar/withdrawals": "0.1.0",
}
```

```php
"autoload": {
    "psr-4": {
        "Codificar\\Withdrawals\\": "vendor/codificar/withdrawals/src/"
    }
}
```
Update project dependencies:

```shell
$ composer update
```

Register the service provider in `config/app.php`:

```php
'providers' => [
  /*
   * Package Service Providers...
   */
  Codificar\Withdrawals\WithdrawalsServiceProvider::class,
],
```


Check if has the laravel publishes in composer.json with public_vuejs_libs tag:

```
    "scripts": {
        //...
		"post-autoload-dump": [
			"@php artisan vendor:publish --tag=public_vuejs_libs --force"
		]
	},
```

Or publish by yourself


Publish Js Libs and Tests:

```shell
$ php artisan vendor:publish --tag=public_vuejs_libs --force
```

- Migrate the database tables

```shell
php artisan migrate
```


## Admin Routes
| Type  | Return | Route  | Description |
| :------------ |:---------------: |:---------------:| :-----|
| `get` | View/html | /admin/libs/cnab_settings/ | Get the cnab settings edit page |
| `post` | Api/json | /admin/libs/cnab_settings/save | Api to save cnab settings | 
| `post` | Api/json | /admin/libs/cnab_settings/create_cnab_file | Api to create a cnab .REM file | 
| `post` | Api/json | /admin/libs/cnab_settings/send_ret_file | Api to upload .RET file | 
| `post` | Api/json | /admin/libs/cnab_settings/delete_cnab_file | Api to delete a cnab .REM  file| 

| Type  | Return | Route  | Description |
| :------------ |:---------------: |:---------------:| :-----|
| `get` | View/html | /admin/libs/withdrawals/ | The view of withdrawals Report |
| `post` | Api/json | /admin/libs/withdrawals/confirm_withdraw | Api to confirm a withdrawal sending a receipt and a date | 
| `get` | Api/json | /admin/libs/withdrawals/download | Api to download the withdrawals report |

| Type  | Return | Route  | Description |
| :------------ |:---------------: |:---------------:| :-----|
| `get` | View/html | /admin/libs/withdrawals_settings/ | The view of withdrawals settings page |
| `post` | Api/json | /admin/libs/withdrawals_settings/save | Api to edit the withdrawals settings |

## Provider Routes (web)
| Type  | Return | Route  | Description |
| :------------ |:---------------: |:---------------:| :-----|
| `get` | View/html | /provider/libs/withdrawals/ | The view of withdrawals settings page of provider |
| `post` | Api/json | /admin/libs/withdrawals/WithdrawAdd | Api to provider add a withdraw |
| `post` | Api/json | /admin/libs/withdrawals/create-user-bank-account | Api to provider create a bank, inside the withdrawal page |
| `get` | Api/json | /admin/libs/withdrawals/download | Api to provider download the withdrawals report |

## Provider Routes (App)
| Type  | Return | Route  | Description |
| :------------ |:---------------: |:---------------:| :-----|
| `post` | Api/json | /libs/withdrawals/report | Api to get the withdrawals report for use in the app |
| `post` | Api/json | /libs/withdrawals/add | Api to provider add a withdraw in the app |
| `post` | Api/json | /libs/withdrawals/settings | Api to provider get the withdrawals settings and show in the app |

## Translation files route
| Type  | Return | Route  | Description |
| :------------ |:---------------: |:---------------:| :-----|
| `get` | Api/json | /libs/lang.trans/{file} | Api to get the translation files of laravel and use inside the vue.js |
