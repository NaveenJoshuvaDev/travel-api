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

A resource controller in Laravel is a controller that handles all the typical "CRUD" (Create, Read, Update, Delete) operations for a given resource. It provides a standard way to manage resources and automatically creates the routes for these operations, which makes it easier to follow RESTful conventions.

### Creating a Resource Controller

You can create a resource controller using the Artisan command:

```bash
php artisan make:controller PhotoController --resource
```

This command will generate a controller with predefined methods corresponding to typical CRUD actions:

```php
<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use Illuminate\Http\Request;

class PhotoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Photo $photo)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Photo $photo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Photo $photo)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Photo $photo)
    {
        //
    }
}
```

### Defining Resource Routes

To define resource routes in your `web.php` or `api.php` routes file, use the `Route::resource` method:

```php
use App\Http\Controllers\PhotoController;

Route::resource('photos', PhotoController::class);
```

This single line of code creates multiple routes to handle various actions on the `photos` resource:

- `GET /photos` - `index` method
- `GET /photos/create` - `create` method
- `POST /photos` - `store` method
- `GET /photos/{photo}` - `show` method
- `GET /photos/{photo}/edit` - `edit` method
- `PUT/PATCH /photos/{photo}` - `update` method
- `DELETE /photos/{photo}` - `destroy` method

### Available Methods

Here are the methods included in a resource controller and their purposes:

- **index:** Display a listing of the resource.
- **create:** Show the form for creating a new resource.
- **store:** Store a newly created resource in storage.
- **show:** Display the specified resource.
- **edit:** Show the form for editing the specified resource.
- **update:** Update the specified resource in storage.
- **destroy:** Remove the specified resource from storage.

### Customizing Resource Routes

If you need to customize the routes generated by `Route::resource`, you can use the `except` or `only` methods:

```php
// Only include specific methods
Route::resource('photos', PhotoController::class)->only(['index', 'show']);

// Exclude specific methods
Route::resource('photos', PhotoController::class)->except(['create', 'edit']);
```

### Naming Resource Routes

Laravel automatically assigns route names to resource routes, which follow the convention of `resource.action`. For example:

- `photos.index`
- `photos.create`
- `photos.store`
- `photos.show`
- `photos.edit`
- `photos.update`
- `photos.destroy`

You can use these names when generating URLs or redirecting:

```php
return redirect()->route('photos.index');
```

Resource controllers in Laravel provide a standardized and efficient way to handle CRUD operations, promoting best practices and maintaining a clean and organized codebase.


### API endPoints

- We now place it as 

```php

Route::get('travels', [TravelController::class , 'index']);

```
- So the Route will be api/travels
- But i need to add api/v1/travels
```php

Route::get('v1/travels', [TravelController::class , 'index']);

```
-But we have to manually write in  every Route this is where we use prefix method and group. 


```php

Route::prefix('v1')->group(

)


```
- But if you have only one version api you can define it in hidden approach set it in route service provider.
- Before Laravel 10 you can go and write manually in route service provider
- I'm given laravel11 example below because it removed route service provider and you shoul go to bootstrap/app.php to set route.

```php

<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix:'api/v1'
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();


```


- Next declare the function in TravelController

```php

<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Travel;

class TravelController extends Controller
{
    //

    public function index()
    {
        return Travel::where('is_public', true)->get();
    }
}


```

```json


id	1
is_public	1
slug	"some-thing"
name	"some thing"
description	"aaa"
number_of_days	5
created_at	"2024-08-13T15:42:30.000000Z"
updated_at	"2024-08-13T15:42:30.000000Z"

```
- Here we get the results but you have to filter the results so now make a Travel Resource .

```php


php artisan make:resource TravelResource


```
- Now to filter Travels define or alter the function in TravelController

```php



 public function index()
    {
        $travels = Travel::where('is_public', true)->get();
        return TravelResource::collection($travels);
    }

```

- So  now in Travel Resource you have to define it.


```php


<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TravelResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
```

- Above function is default one
 - now we must change it to what we need in toArray function

 ```php


  public function toArray(Request $request): array
    {
       return [
           'id' => $this->id,
           'name' => $this->name,
           'slug' => $this->slug,
           'description' => $this->description,
           'number_of_days' => $this->number_of_days,
           'number_of_nights' => $this->number_of_nights,
       ];
    }


```
- After Defining the output will be.
```json
{
    "data": [
        {
            "id": 1,
            "name": "some thing",
            "slug": "some-thing",
            "description": "aaa",
            "number_of_days": 5,
            "number_of_nights": 4
        }
    ]
}

```
- Above the wrapper Data form eloquent API response ,you can disable that,but its more useful and it allows you to add more information on top of data not only for data but for 
information about pagination a

- To add pagination 

```php

  $travels = Travel::where('is_public', true)->paginate();

```
- below is the results in JSON

```php

{
    "data": [
        {
            "id": 1,
            "name": "some thing",
            "slug": "some-thing",
            "description": "aaa",
            "number_of_days": 5,
            "number_of_nights": 4
        }
    ],
    "links": {
        "first": "http://127.0.0.1:8000/api/v1/travels?page=1",
        "last": "http://127.0.0.1:8000/api/v1/travels?page=1",
        "prev": null,
        "next": null
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 1,
        "links": [
            {
                "url": null,
                "label": "&laquo; Previous",
                "active": false
            },
            {
                "url": "http://127.0.0.1:8000/api/v1/travels?page=1",
                "label": "1",
                "active": true
            },
            {
                "url": null,
                "label": "Next &raquo;",
                "active": false
            }
        ],
        "path": "http://127.0.0.1:8000/api/v1/travels",
        "per_page": 15,
        "to": 1,
        "total": 1
    }
}


```
- Now you see How data is beneficial to add more information it allows you to add any messages
any extra information for the API clients


### php unit Test

 - As soons as you build a new feature you can either test with php uinit or PEST in this you can gonna do with PHP unit Test.

- First test name is travelslist
```php

php artisan make:test TravelsListTest
```

- Automated Test is not about syntax
- Defining the condition in our Thoughts and declaring it.
- Here we test that `Data is returned correctly` and whether it it filters `is_public`.


1.***create Factory***
```php
  php artisan make:factory TravelFactory --model=Travel

```
- In Travel Factory you should define this and type the artisan command to access TRAVEL MODAL CLASS

```php
php artisan make:factory TravelFactory --model=Travel

 public function definition(): array
    {
        return [
            //

                  'name' => fake()->text(20),
                'is_public' => fake()->text(20),

                'description' => fake()->text(100),
                'number_of_days' => rand(1, 10)

        ];
    }


```

- for define database virutally which shouldn't affect the production database in Phpunit.xml add DBconnection and Database
- Below we have written our first Test condition.

```php


class TravelsListTest extends TestCase
{
  use RefreshDatabase;
    public function test_travels_list_returns_paginated_data_correctly(): void
    {

        //we have to create 16 records
        //only 15 of them returned with data
        //with two pages
        //so thats why we create 16 records
        Travel::factory(16)->create(['is_public' => true]);
        $response = $this->get('/api/v1/travels');

        $response->assertStatus(200);
        $response->assertJsonCount(15, 'data');
        $response->assertJsonPath('meta.last_page', 2);
    }
}

```
2. ****Explaining the first Test Cases****

This Laravel test case is designed to verify that the `travels_list` endpoint returns paginated data correctly. Let's break it down step by step:

1. **Class Definition and Setup**:
   ```php
   class TravelsListTest extends TestCase
   {
       use RefreshDatabase;
   ```

   - The `TravelsListTest` class extends `TestCase`, which means it inherits the functionality needed for Laravel's testing framework.
   - The `use RefreshDatabase;` line ensures that the database is reset after each test, which helps maintain a clean state for testing.

2. **Test Method**:
   ```php
   public function test_travels_list_returns_paginated_data_correctly(): void
   ```

   - The `test_travels_list_returns_paginated_data_correctly` method is where the actual test logic is implemented. The `void` return type indicates that this method doesn't return any value.

3. **Creating Test Data**:
   ```php
   Travel::factory(16)->create(['is_public' => true]);
   ```

   - This line uses a factory to create 16 `Travel` records in the database. The `is_public` attribute is set to `true` for all these records. This step simulates having 16 travel entries in the system.

4. **Sending a GET Request**:
   ```php
   $response = $this->get('/api/v1/travels');
   ```

   - A GET request is sent to the `/api/v1/travels` endpoint, which is assumed to be the endpoint that returns the list of travels.

5. **Asserting the Response**:
   ```php
   $response->assertStatus(200);
   ```

   - This assertion checks that the response status is `200`, which means the request was successful.

   ```php
   $response->assertJsonCount(15, 'data');
   ```

   - This assertion checks that the JSON response contains 15 items in the `data` field. Since pagination is involved, only 15 out of the 16 records are returned on the first page.

   ```php
   $response->assertJsonPath('meta.last_page', 2);
   ```

   - This assertion checks that the `meta.last_page` field in the JSON response indicates there are 2 pages. Given that there are 16 records and only 15 are returned per page, there should indeed be 2 pages.

### Summary

This test case ensures that:

1. The `/api/v1/travels` endpoint returns the correct number of items per page (15 in this case).
2. The total number of pages is calculated correctly (2 pages for 16 items).
3. The response status is `200`, indicating a successful request.

By running this test, you can confirm that the pagination logic in your `travels_list` endpoint works as expected.


3. ***SecondTestcase***
- It should return only `is_public` records that to be only one.
```php



 public function test_travels_list_shows_only_public_records(): void
    {


        $publicTravel = Travel::factory()->create(['is_public' => true]);
        Travel::factory()->create(['is_public' => false]);
        $response = $this->get('/api/v1/travels');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.name',$publicTravel->name);
    }


    ```

****Explaining the second Test Cases****
This Laravel test case is designed to verify that the `travels_list` endpoint only shows public travel records. Here's a step-by-step explanation of what the test does:

### Class and Method Definition

```php
public function test_travels_list_shows_only_public_records(): void
{
```

- This method is named `test_travels_list_shows_only_public_records` and its purpose is to ensure that the API endpoint `/api/v1/travels` returns only public travel records.
- The `void` return type indicates that this method does not return any value.

### Creating Test Data

```php
$publicTravel = Travel::factory()->create(['is_public' => true]);
Travel::factory()->create(['is_public' => false]);
```

- The first line creates a public `Travel` record using a factory, with the `is_public` attribute set to `true`. The created record is stored in the `$publicTravel` variable.
- The second line creates another `Travel` record, but this time with the `is_public` attribute set to `false`.

### Sending a GET Request

```php
$response = $this->get('/api/v1/travels');
```

- A GET request is sent to the `/api/v1/travels` endpoint, which is expected to return a list of travels.

### Asserting the Response

```php
$response->assertStatus(200);
```

- This assertion checks that the response status is `200`, indicating a successful request.

```php
$response->assertJsonCount(1, 'data');
```

- This assertion checks that the JSON response contains exactly 1 item in the `data` field. Since only the public travel record should be returned, the count should be 1.

```php
$response->assertJsonPath('data.0.name', $publicTravel->name);
```

- This assertion checks that the `name` attribute of the first item in the `data` array matches the `name` of the `$publicTravel` record created earlier. This confirms that the returned record is indeed the public travel record.

### Summary

This test case ensures that:

1. The `/api/v1/travels` endpoint only returns public travel records.
2. The total number of public travel records returned is correct (in this case, 1).
3. The details of the returned public travel record are accurate and match the expected public record.

By running this test, you can confirm that the endpoint correctly filters out non-public travel records and only shows those that are meant to be publicly visible.


### Public Endpoint for Tours with Tests

A public (no auth) endpoint to get a list of paginated tours by the travel slug (e.g. all the tours of the travel foo-bar). Users can filter (search) the results by priceFrom, priceTo, dateFrom (from that startingDate) and dateTo (until that startingDate). User can sort the list by price asc and desc. They will always be sorted, after every additional user-provided filter, by startingDate asc.

- Make Api Controller and Resource

```php

php artisan make:controller Api/V1/TourController

php artisan make:resource TourResource

```
- Route::get('travels/{travel}/tours', [TourController::class , 'index']);
- Above Route would do route model binding ,it search through id by default, but client wnats to search through slug

```php

Route::get('travels/{travel:slug}/tours', [TourController::class , 'index']);

```

- `get('travels/{travel:slug}/tours')` you can define this globally for all route model binding call.

- By defining below function in in travle model

```php

public function getRouteKeyName()
{
    return 'slug';
}
```
- now you can specify like below to access it



```php

Route::get('travels/{travel}/tours', [TourController::class , 'index']);

```
- Route::get('travels/{travel:slug}/tours', [TourController::class , 'index']); using this all developers will get know the exact purpose.


```json


{
    "data": [
        {
            "id": 1,
            "travel_id": 1,
            "name": "kerala",
            "starting_date": "2024-06-11",
            "endind_date": "2024-06-16",
            "price": 10500,
            "created_at": null,
            "updated_at": null
        }
    ],
    "links": {
        "first": "http://127.0.0.1:8000/api/v1/travels/some-thing/tours?page=1",
        "last": "http://127.0.0.1:8000/api/v1/travels/some-thing/tours?page=1",
        "prev": null,
        "next": null
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 1,
        "links": [
            {
                "url": null,
                "label": "&laquo; Previous",
                "active": false
            },
            {
                "url": "http://127.0.0.1:8000/api/v1/travels/some-thing/tours?page=1",
                "label": "1",
                "active": true
            },
            {
                "url": null,
                "label": "Next &raquo;",
                "active": false
            }
        ],
        "path": "http://127.0.0.1:8000/api/v1/travels/some-thing/tours",
        "per_page": 15,
        "to": 1,
        "total": 1
    }
}


```
- By default it will show everything we have to change it in tour resource


```php


<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TourResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}

```
- see we have change like below whatwe need that by client request

```php

 public function toArray(Request $request): array
    {
        //return parent::toArray($request);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'starting_date' => $this->starting_date,
            'ending_date' => $this->ending_date,
            'price' => number_format($this->price, 2),
        ];
    }


```
- compare above data and below one here we have implemented number_Format that transform that to string

```json


{
    "data": [
        {
            "id": 1,
            "name": "kerala",
            "starting_date": "2024-06-11",
            "ending_date": null,
            "price": "10,500.00"
        }
    ],
    "links": {
        "first": "http://127.0.0.1:8000/api/v1/travels/some-thing/tours?page=1",
        "last": "http://127.0.0.1:8000/api/v1/travels/some-thing/tours?page=1",
        "prev": null,
        "next": null
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 1,
        "links": [
            {
                "url": null,
                "label": "&laquo; Previous",
                "active": false
            },
            {
                "url": "http://127.0.0.1:8000/api/v1/travels/some-thing/tours?page=1",
                "label": "1",
                "active": true
            },
            {
                "url": null,
                "label": "Next &raquo;",
                "active": false
            }
        ],
        "path": "http://127.0.0.1:8000/api/v1/travels/some-thing/tours",
        "per_page": 15,
        "to": 1,
        "total": 1
    }
}


```
- So now we had created a new feature ,now lets do automated tests.

### Second Tests TourListTest

create Test File.

```php
 php artisan make:test ToursListTest
```
What to Test Make scenarios?

1. Similar to travels check whether pagination is returned correctly 
2. whether price is returned correctly with cents 
3. whether the slug works the route model binding and filters the record by travel slug


```php

php artisan make:factory TourFactory --model=Tour
```

- Default pagination is 15pages so to over come that use this below config
```php


  public function test_tours_list_returns_pagination(): void
    {
        $toursPerPAge = config('app.paginationPerPage.tours');

        $travel = Travel::factory()->create();
        Tour::factory($toursPerPage + 1)->create(['travel_id' => $travel->id]);

        $respone = $this->get('api/v1/travels/'.$travel->slug.'/tours');

        $response->assertStatus(200);
        $response->assertJsonCount($toursPerPage, 'date');
        $response->assertJsonPath('meta.current_page', 1);

    }


```


### returned a error at test
- sorry  guys i created atable columnas endind_date instead of ending_date thats i solved you may also slove that by changing in Tour model.


```php

see migration file
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
- `Tour Model`
```php
change here
  protected $fillable = [
           'travel_id',
           'name',
           'starting_date',
           'endind_date',->change here
           'price',
    ];

```
```php



   FAIL  Tests\Feature\ToursListTest
  ⨯ tours list by travel slug returns correct tours                                                                                                                                0.59s  

   PASS  Tests\Feature\TravelsListTest
  ✓ travels list returns paginated data correctly                                                                                                                                  0.12s  
  ✓ travels list shows only public records                                                                                                                                         0.04s  
  ──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────  
   FAILED  Tests\Feature\ToursListTest > tours list by travel slug returns correct tours                                                                                 QueryException   
  SQLSTATE[HY000]: General error: 1 table tours has no column named ending_date (Connection: sqlite, SQL: insert into "tours" ("name", "starting_date", "ending_date", "price", "travel_id", "updated_at", "created_at") values (Architecto sed quas., 2024-08-20 11:40:52, 2024-08-27 11:40:52, 35728, 1, 2024-08-20 11:40:52, 2024-08-20 11:40:52))

  at vendor\laravel\framework\src\Illuminate\Database\Connection.php:825
    821▕                     $this->getName(), $query, $this->prepareBindings($bindings), $e
    822▕                 );
    823▕             }
    824▕
  ➜ 825▕             throw new QueryException(
    826▕                 $this->getName(), $query, $this->prepareBindings($bindings), $e
    827▕             );
    828▕         }
    829▕     }

  1   vendor\laravel\framework\src\Illuminate\Database\Connection.php:825

  2   vendor\laravel\framework\src\Illuminate\Database\Connection.php:565
      NunoMaduro\Collision\Exceptions\TestException::("SQLSTATE[HY000]: General error: 1 table tours has no column named ending_date")


  Tests:    1 failed, 2 passed (6 assertions)
  Duration: 1.04s


  ```


- `Test PAssed 5`

```php


   PASS  Tests\Feature\ToursListTest
  ✓ tours list by travel slug returns correct tours                                                                                                                                0.62s  
  ✓ tour price is shown correctly                                                                                                                                                  0.04s  
  ✓ tours list returns pagination                                                                                                                                                  0.05s  

   PASS  Tests\Feature\TravelsListTest
  ✓ travels list returns paginated data correctly                                                                                                                                  0.08s  
  ✓ travels list shows only public records                                                                                                                                         0.03s  

  Tests:    5 passed (15 assertions)
  Duration: 1.12s






```
- You want perform test separately do this.

- checkout ` php artisan test --filter=ToursListTest`

- ***This part-3 video notice: you may also define $perPage in the TourModel but i don't know where***


### The Remaining part of public end point with tours with tests

Users can filter (search) the results by priceFrom, priceTo, dateFrom (from that startingDate) and dateTo (until that startingDate). User can sort the list by price asc and desc. They will always be sorted, after every additional user-provided filter, by startingDate asc.


```php


public function index(Travel $travel)
    {
     

        $tours = $travel->tours()
        //add here
        //date from or date to
        //price from or price 2
        //order by price ascending 
        //order by price descending
        ->orderBy('starting_date')
        ->paginate();
     return TourResource::collection($tours);
    }



```

- date from or date to
- price from or price 2
- order by price ascending 
- order by price descending


- Below solution normal people do
```php
$query = $travel->tours();
if(request('dateFrom')){
$query->where();
}
$tours = $query
->orderBy('starting_date')
->paginate();
 return TourResource::collection($tours);

```
- But eloquent has more convenient way

```php
 public function index(Travel $travel, Request $request)
    {
$tours =$travel->tours()
->when($request->dateFrom, function ($query) use ($request){
    $query->where('starting_date','>=', $request->dateFrom);
})


    }
```
- Below is the final version

```php
 public function index(Travel $travel, Request $request)
    {

$tours =$travel->tours()
->when($request->priceFrom, function ($query) use ($request){
    $query->where('price','>=', $request->priceFrom * 100);
})

->when($request->priceTo, function ($query) use ($request){
    $query->where('price','<=', $request->priceTo * 100);
})
->when($request->dateFrom, function ($query) use ($request){
    $query->where('starting_date','>=', $request->dateFrom);
})
->when($request->dateTo, function ($query) use ($request){
    $query->where('starting_date','<=', $request->dateTo);
})


->orderBy('starting_date')
->paginate();
 return TourResource::collection($tours);

    }

```

- now we gonna make sorting Price  for asc orderby

```php



->when($request->sortBy && $request->sortOrder, function ($query) use ($request){
     $query->orderBy($request->sortBy, $request->orderBy);
})

```
***we have to  provide solution for error notice `validation error`***

- It should not display 500 error directly ,validate a possible solution for an api client.
```php



http://127.0.0.1:8000/api/v1/travels/some-thing/tours?dateFrom=2024-06-11&dateTo=2024-06-19&priceFrom=99&priceTo=20000&sortBy=price&sortOrder=random
```

- Above i placed random for order.

- first way

```php

->when($request->sortBy && $request->sortOrder, function ($query) use ($request){
    if(!in_array($request->sortOrder, ['asc', 'desc'])) return;
     $query->orderBy($request->sortBy, $request->orderBy);
})

```
- second way using `$request->validate([]);`
```php


$request->validate([
    'priceFrom' => 'numeric',
    'priceTo' => 'numeric',
    'dateFrom' => 'date',
    'dateTo' => 'date',
    'sortBy' => Rule::in(['price']),
    'sortOrder' => Rule::in(['asc', 'desc']),
]);


```
- validation
```php

$validated = $request->validate([
    'priceFrom' => 'numeric|nullable',
    'priceTo' => 'numeric|nullable',
    'dateFrom' => 'date|nullable',
    'dateTo' => 'date|nullable',
    'sortBy' => [
        'nullable',
        Rule::in(['price']),
    ],
    'sortOrder' => [
        'nullable',
        Rule::in(['asc', 'desc']),
    ],
], [
    'sortBy.in' => 'Only price value is accepted for sorting.',
    'sortOrder.in' => 'Only asc or desc values are accepted for sort order.',
]);


```

- Next we have to make Tourslistrequest.

`php artisan make:request ToursListRequest`
