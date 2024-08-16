<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a>API</p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel Travel API

- First Create the Database Schema to the first built ORM  Models  Using Eloquent .


```php

php artisan make:model Role -m
```
-  Second You have to create a Pivot Table for Relationship 

```php
   php artisan make:migration create_role_user_table

```
- Below is the migration file content

```php
 Schema::create('role_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained();
             //above line short reference of ,constrained is a shorter way to express you can also provide parameters in constrained
             //$table->foreignId('user_id')->references('id')->on('roles');
            $table->foreignId('user_id')->constrained();

            $table->timestamps();
        });


```
- Third add Many to Many Reletionships in users Model 
```php

 public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

```
- Fourth Step is to create  a Model For Travel
```php

php artisan make:model Travel -m

```
- When i create an Model its Plural version is Named for Tables for eg: User Model , Users Table. Its Laravel Naming Convention but for ***Travel*** its plural is ***travel*** only its a ***irregular noun*** when i checked in tinker it also says that checkout below

```php

php artisan tinker
Psy Shell v0.12.4 (PHP 8.2.0 — cli) by Justin Hileman
> str('travel')->plural();                                                                                                                                                                                    
= Illuminate\Support\Stringable {#5038
    value: "travel",
  }
```
- Now travels,users,roles,role_user tables had been created
- Now we want to automatically generate slug using name with help of observer ,but you can create unique names with observer.
- Learn observer in laravel what they can do
- We have package called ***Cviebrock*** used to detect automatic slug and in a unique name.

- Next step is to create a virtual number of nights column(which is not a database column calculated computed column which in eloquent terms is accessor) for calculationg number of days stay in an hotel.
- so we create an accessor of number of days in the travel model.
- Below is the Travel model php file

```php
<?php

namespace App\Models;


use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Travel extends Model
{
    use HasFactory, Sluggable;

    protected $table= 'travels';
    protected $fillable = [
       'is_public',
       'slug',
       'name',
       'description',
       'number_of_days',
    ];


    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }

    public function numberOfNights(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes)=> $attributes['number_of_days']-1);
    }
}


```
We have Created an virtual column by making accessor and getting results ,we can see the results through Tinker


```php

Psy Shell v0.12.4 (PHP 8.2.0 — cli) by Justin Hileman
> App\Models\Travel::create(['name'=>'good thing','description' => 'aaa', 'number_of_days' => 5]);                                                                                 
= App\Models\Travel {#5058
    name: "good thing",
    description: "aaa",
    number_of_days: 5,
    slug: "good-thing",
    updated_at: "2024-08-14 05:56:56",
    created_at: "2024-08-14 05:56:56",
    id: 3,
  }

> $travel = Travel::latest()->first();                                                                                                                                             
[!] Aliasing 'Travel' to 'App\Models\Travel' for this Tinker session.
= App\Models\Travel {#5041
    id: 3,
    is_public: 0,
    slug: "good-thing",
    name: "good thing",
    description: "aaa",
    number_of_days: 5,
    created_at: "2024-08-14 05:56:56",
    updated_at: "2024-08-14 05:56:56",
  }

> $travel->number_of_nights;                                                                                                                                                       
= 4

//Study Laravel accessor and mutator
```
- by creating Travel instance and calling we done that accessor.
- Study Laravel accessor and mutator which is get some attribute and set some attribute.
- Below is the older version of syntax getting attribute
```php

  public function getNumberOfNightsAttribute()
    {
        return $this->number_of_days - 1;
    }

```
### create Tours Table
```php
php artisan make:model Tour -m
```

- While defining Models of the Tour 

```php

  protected $fillable = [
           'travel_id',
           'name',
           'starting_date',
           'ending_date',
           'price',
    ];

```
- While defining Migration we can easily get error because constrained or reference of the another  Table  error plural form of travels which is shown below

```php
  Schema::create('tours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('travel_id')->constrained('travels');
            $table->string('name');
            $table->date('starting_date');
            $table->date('endind_date');
            $table->integer('price');
            $table->timestamps();
        });



```
- Noramlly we don't have define the constrained or Reference Laravel will detect the reference automatically

```php
 $table->foreignId('travel_id')->constrained();

 ```
 - why it can't detect it, because of irregular noun of then name ***Travel*** Model ,so we have to manually  define it.

- The error will be SQLSTATE[HY000]: General error:1824 Failed to open the referenced table 'travel',foreign key('travel_id) references 'travel' ('id)
- define relationship in Travel.php
```php

 public function tours(): HasMany
    {
        return $this->hasMany(Tour::class);
    }

```

- next Whenever the money value is stored in database you have to store it as an integer must,but in client view you have to show it as decimal
- for that purpose you have to use accessor Attribute in Tours
```php

   public function price(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100
        );
    }

```
- what we are doing here is writing getter and setter condition. to show it as decimal value for client.
- Next incremental ID with primary key or uuids
- how to define uuids
```php

$table->uuid('id')->primary();
```
- For foreign key reference using uuids

```php
$table->foreignUuid('travel_id')->constrained('travels');


```
- But in personal access tokens use
```php

$table->morphs('tokenable');
to
$table->uuidMorphs('tokenable')
```
- And in all models define HasUuids,


### Create API endpoints

- A public (no auth) endpoint to get a list of paginated travels.It must return only ***public*** travels.

How to create an API?
```php

php artisan make:controller Api/V1/TravelController

```
- Now we gonna define Route controller in Laravel11 you have to install it.

```php




php artisan install:api

```
- API scaffolding installed. Please add the [Laravel\Sanctum\HasApiTokens] trait to your User model. 

- You must aware of trait.

- Define routes in API.
- study about invokable controller and resource controller.

### Invokable Controller

In Laravel, an "invokable controller" refers to a controller class that contains a single `__invoke` method. This method is called when the controller is used as a single action controller. This simplifies the controller and makes the code cleaner and more readable when you need a controller to handle just one specific route or functionality.

Here's an example of how you can create an invokable controller in Laravel:

1. **Create the Controller:**

   You can create an invokable controller using the Artisan command line tool:

   ```bash
   php artisan make:controller MyController --invokable
   ```

   This command will generate a controller with a single `__invoke` method:

   ```php
   <?php

   namespace App\Http\Controllers;

   use Illuminate\Http\Request;

   class MyController extends Controller
   {
       /**
        * Handle the incoming request.
        *
        * @param  \Illuminate\Http\Request  $request
        * @return \Illuminate\Http\Response
        */
       public function __invoke(Request $request)
       {
           // Your logic here
       }
   }
   ```

2. **Define the Route:**

   In your `web.php` or `api.php` routes file, you can define a route that uses this controller:

   ```php
   use App\Http\Controllers\MyController;

   Route::get('/my-route', MyController::class);
   ```

   Here, when the `/my-route` URL is accessed, the `__invoke` method of the `MyController` will be called.

**Benefits of using Invokable Controllers:**

- **Simplicity:** If you only need a controller to handle a single action, using an invokable controller makes the code cleaner and reduces boilerplate.
- **Readability:** It makes the intention clear that the controller is meant for a single purpose.
- **Organization:** Keeps your controller code organized and concise, especially for simple operations.

Invokable controllers are particularly useful for handling simple routes, such as those for basic API endpoints, form submissions, or single-page views where a full-fledged resource controller might be overkill.


### Resource Controller
