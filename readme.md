### Features
Demo ldap in laravel 5.4 by adldap2/adldap2-laravel v 3.0 , Active Directory 
# How to install
Use powershell or cmd and type by order, please see below.
- `git clone https://github.com/kantinanm/laravel-ldap.git`
- `cd laravel-ldap`
- > install package dependency in this project.
    `composer install`
- > create config.js and modify value.
  `cp .env.example .env` 
  > In windows use command `copy .env.example .env` 
  > generate key in this project.
    `php artisan key:generate`
  > at config.js file to modify value, 
  ```php
    ADLDAP_CONNECTION=default
    ADLDAP_CONTROLLERS=// endpoint server Active Directory 
    ADLDAP_BASEDN=// eg. dc=nu,dc=local
    ADLDAP_USER_ATTRIBUTE=samaccountname
    ADLDAP_USER_FORMAT=samaccountname=%s,dc=nu,dc=local
    ADLDAP_ADMIN_USERNAME=// account
    ADLDAP_ADMIN_PASSWORD=// password
    ADLDAP_ACCOUNT_SUFFIX=// eg @nu.ac.th
  ``` 
- > migrate database ,please config .env (DB_DATABASE, DB_USERNAME,DB_PASSWORD) before run command below.
    `php artisan migrate`
- ``php artisan serve`


# Test URL
http://localhost:8000/login


