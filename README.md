# Data-Transformer Package for Laravel

[![Latest Stable Version](https://poser.pugx.org/ipunkt/data-transformer/v/stable.svg)](https://packagist.org/packages/ipunkt/data-transformer) 
[![Latest Unstable Version](https://poser.pugx.org/ipunkt/data-transformer/v/unstable.svg)](https://packagist.org/packages/ipunkt/data-transformer) 
[![License](https://poser.pugx.org/ipunkt/data-transformer/license.svg)](https://packagist.org/packages/ipunkt/data-transformer) 
[![Total Downloads](https://poser.pugx.org/ipunkt/data-transformer/downloads.svg)](https://packagist.org/packages/ipunkt/data-transformer)

Data will be manipulated from production (source) to staging (target). This tool will be definitely needed when 
you dislike to work with real data from your users.

The main job of this package to fake data that GDPR relevant is. Take a look at the following List:

* Name
* Email Address
* Phone number
* Credit cards
* Date of birth
* Place of birth
* Identification number
* Online data
    * IP address
    * Location data (GPS)
* Images
* License plate
* Health data

This list must be considered, when you try to work with real Data, therefore  GDPR relevant data must be transformed. 
The Rest will be then `1:1` taken.


### Installation - Quick-start
 `composer require ipunkt/data-transformer`
 
__Or__

 Alternative you can add these lines into your composer file then `composer install` in console command
```
"require": {
	"ipunkt/data-transformer": "^1.0"
}
```

### Configuration
I assume that you already have a connection in your `database.php`. like the following
 ```
'mysql' => [
        'driver' => 'mysql',
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '3306'),
        'database' => env('DB_DATABASE', 'forge'),
        'username' => env('DB_USERNAME', 'forge'),
        'password' => env('DB_PASSWORD', ''),
        'unix_socket' => env('DB_SOCKET', ''),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'prefix_indexes' => true,
        'strict' => true,
        'engine' => null,
    ],
```
You will be asked about the connection which is for source, when you run later on both of commands.
`php artisan transform:dump` and `php artisan transfrom:data`


* run this command: `php artisan vendor:publish` then choose `Ipunkt\DataTransformer\DataTransformerServiceProvider`
* find your Config File in `config/data-transformer.php` 
* edit your Config for instance: `name` to `username` and/or `fakeName` to `value` or vice versa
* run `php artisan transform:dump {host} {db} {username} {password}` your standard config File will be `data-transformer.json` 
you'll find it in the root of your Application.
An Example `php artisan transform:dump 000.000.0.000 transformer root pw`
* `000.000.0.000` --> `IP address` as `host`
* `transformer` --> `DB_NAME`
* `root` --> `USERNAME`
*  `pw` --> `PASSWORD`
in `data-transformer.json` you'll find something like this:
 ```
{
  "users": {
    "id": "value",
    "name": "fakeName",
    "email": "fakeEmail",
    "action_on_redeem_json": "value",
    "action_on_expire_json": "value",
    "created_at": "value",
    "updated_at": "value"
  }
}
```

Here's the all list with data that could be transformed:

* `name` => `fakeName` via faker `$this->faker->name`
* `email` => `fakeEmail` via faker `$this->faker->safeEmail`
* `place_of_birth` => `fakePlaceOfBirth` via faker `$this->faker->country`
* `data_health` => `fakeDataHealth` via faker `$this->faker->randomDigit`
* `id_number` => `fakeID` via faker `$this->faker->uuid`
* `phone_number` => `fakePhoneNumber` via faker `$this->faker->phoneNumber`
* `credit` => `fakeCredit` via faker `$this->faker->bankAccountNumber`
* `license_plate` => `fakeLicensePlate` via faker `$this->faker->randomLetter`
* `image` => `fakeImage` via faker `$this->faker->image`
* `ip_address` => `fakeIPAddress` via faker `$this->faker->localIpv4`
* `data_location` => `fakeDataLocation` via faker `$this->faker->latitude`
* `address` => `fakeAddress` via faker `$this->faker->address`
* `date_of_birth` => `fakeDateOfBirth` via faker `$this->faker->dateTime()->format('Y-m-d')`

Here you can decide whether the Name must be transformed or not, for instances. If you let the Name without any change then it will be faked. If you don't want to transform Name, then you have to replace the `fakeName` with `value`. That's it.

The second and last Step:
run the second command `php artisan transform: {host} {db} {username} {password}` (like `transform:dump` command)  
`{--target=mysql}` 

you have to change the `mysql` to whatever it is in your `database.php`

If you want to disable foreign keys that tables has/have, add the following flag `foreign-keys-checks` 
at the end of the second Command:
`php artisan transform:dump {source} {target} --foreign-keys-checks=no`

__Note:__  
if you changed your config file, then it is required otherwise you don't need to do anything else.



And you're Done!


