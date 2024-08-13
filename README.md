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
- When i create an Model its Plural version is Named for Tables for eg: User Model , Users Table. Its Laravel Naming Convention but for ***Travel*** its plural is ***travel*** only when i checked in tinker it also says that checkout below

```php

php artisan tinker
Psy Shell v0.12.4 (PHP 8.2.0 — cli) by Justin Hileman
> str('travel')->plural();                                                                                                                                                                                    
= Illuminate\Support\Stringable {#5038
    value: "travel",
  }
```
