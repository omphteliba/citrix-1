Citrix API - PHP wrapper around GoToWebinar APIs
================================================
PHP wrapper for Citrix GoToWebinar API. The library allows simple OAuth or Direct authentication.

Install via Composer
--------------------

```bash
composer require "dalpras/citrix"
```

Authentication with Citrix
--------------------------
All you need in order to authenticate with Citrix's API is a consumer key, which you can obtain by registering at [Citrix Developer Center](https://goto-developer.logmeininc.com/user/me/apps).  

After registering and creating your own application, you will get:
-   `ConsumerKey`, 
-   `ConsumerSecret`

You need the `ConsumerKey` for your application to authenticate with Citrix using this library.  

For authentication you can use a **Direct** or **OAuth** method. 
The last allows you to not store the passwords in your application and interact with much more speed.

If you want to use the **OAuth Authentication**, you need to set also a `CallbackURL` when you create the credentials for your APP.
The `CallbackURL` is the address where the Citrix engine redirect the user after authentication. 
The `CallbackURL` is the place where your application can get a `responseKey` for getting the **APP Authentication Credentials**.

The principles of operation are explained in [How to Get an Access Token and Organizer Key](https://goto-developer.logmeininc.com/how-get-access-token-and-organizer-key).

**APP Authentication Credentials** consists of:
 - `access_token`
 - `organizer_key`

These are used to get responses from the *Citrix GoToWebinar Platform*.

### Direct Authentication
In addition to the `ConsumerKey`, for **Direct Authentication** you need your `username` and `password`, 
which is the one that you use to login into [GoToWebinar.com](https://global.gotowebinar.com/webinars.tmpl).

You can setup a Direct authentication adapter like so:

```php	
$auth = new \Citrix\Auth\Direct('CONSUMER_KEY');
$auth->setUsername('USERNAME')->setPassword('PASSWORD');
```

You can retrieve the **APP Authentication Credentials** like this:

```php	
$auth->applyCredentials();
$accessToken = $auth->getAccessToken(); //returns your access token
$organizerKey = $auth->getOrganizerKey(); //returns the organizer key
```

The code will handle all the authentication stuff for you, so you don't really have to worry about that. 


### OAUTH Authentication
This is the best way to get data from *Citrix GoToWebinar Platform* for security (you don't store passwords) and speed (you don't need to authenticate at each request).  
In order to use the **OAUTH Authentication** you need to give your own `username` and `password`.  

Here are the steps:

 - Grab the Url given from *Citrix GoToWebinar Platform* with the method `getAuthorizationLogonUrl()`
 - Follow the Url, you will be asked to insert the `username` and `password` 
 - You will be redirected to the `CallbackURL`, it will be added of a `code`,
 - Grab the `code` which is the `responseKey` that is needed for getting the **APP Authentication Credentials** (`access_token` and `organizer_key`).  
You can get the needed with:

```php	
// start your application with an OAUth adapter
$auth = new \Citrix\Auth\OAuth('CONSUMER_KEY');

// Get from Citrix the Url where to insert your admin credentials
$redirectUrl = $auth->getAuthorizationLogonUrl();

// redirect
header("Location: $redirectUrl");
```

Then insert the credentials, you will be redirected to `CallbackUrl`

```php

// parse the query string to get the responseKey
parse_str($_SERVER['QUERY_STRING'], $query);
$responseKey =  $query['code'];

/* @var $auth \Citrix\Auth\OAuth */
$auth = $citrix->getAuth();

// set the response key and apply for having the 'access_token' and 'organized_key'
// it's important to set the responseKey before applying credentials otherwise it will not proceed
$auth->setResponseKey($responseKey)->applyCredentials();

// finally we get our APP credentials
$accessToken = $auth->getAccessToken(); //returns your access token
$organizerKey = $auth->getOrganizerKey(); //returns the organizer key
```

The `applyCredentials()` is equivalent to:

```bash
curl -X POST -H "Accept:application/json" -H "Content-Type: application/x-www-form-urlencoded" "https://api.getgo.com/oauth/access_token" -d 'grant_type=authorization_code&code={responseKey}&client_id={consumerKey}'
```

Request parameters explanation:  

|Parameter |Description                              |Format|Required|
|:---------|:----------------------------------------|:-----|:-------|
|grant_type|string reading "authorization_code"      |string|required|
|code      |responseKey from the redirect            |string|required|
|client_id |the application client_id or Consumer Key|string|required|

Response data Example:  

```json
{
 "access_token":"RlUe11faKeyCWxZToK3nk0uTKAL",
 "expires_in":"30758399",
 "refresh_token":"d1cp20yB3hrFAKeTokenTr49EZ34kTvNK",
 "organizer_key":"8439885694023999999",
 "account_key":"9999982253621659654",
 "account_type":"",
 "firstName":"Mahar",
 "lastName":"Singh",
 "email":"mahar.singh@singhSong.com",
 "platform":"GLOBAL",
 "version":"2"
}
```
Response parameters explanation:

|Variable     |Description                                                          |
|:------------|:--------------------------------------------------------------------|
|access_token |OAuth access token                                                   |
|expires_in   |The access token's expiration time in seconds (typically 356 days)   |
|refresh_token|The token to use to obtain a new access token, for example, if the current access token has expired. The refresh token is valid for 13 months. How to Use Refresh Tokens describes how to use it.                          |
|organizer_key|GoTo product user organizer key                                      |
|account_key  |GoTo product account key (may be blank)                              |
|account_type |GoTo product type “personal” or “corporate” (may be missing or blank)|
|firstName    |GoTo product user organizer first name (only G2M, missing or blank for other products)|
|lastName     |GoTo product user organizer last name (only G2M, missing or blank for other products)|
|email        |GoTo product user organizer email (only G2M, missing or blank for other products)|
|platform     |The platform the user's GoTo product account is on ("GLOBAL")        |
|version      |The version of the access token                                      |


POST, PUT, GET, DELETE data in Citrix GoToWebinar Platform
----------------------------------------------------------
After authentication you can get all you need with the followings.

### Getting upcoming webinars
In order to get all the upcoming webinars, you can write:

```php	
$citrix = new \Citrix\Citrix($auth); 
$webinars = $citrix->getUpcoming();
//this gives you all upcoming webinars
var_dump($webinars); 
```

### Creating a webinar
In order to create a webinar, you can write:
	
```php	
$citrix = new \Citrix\Citrix($auth); 
$params = new Citrix\Entity\Webinar\Post();
$params ->setSubject('My title')
        ->setDescription('My title')
        ->setStartTime(new DateTime('2020-10-31 09:00:00'))
        ->setEndTime(new DateTime('2020-10-31 10:00:00'))
    ;
$webinarData    = $citrix->createWebinar($params);
```

All the conversions to UTC are handled by the Params objects.

### Getting past webinars
In order to get all the past webinars, you have to do this:

```php
$citrix = new \Citrix\Citrix($auth); 
$webinars = $citrix->getPast(new DateTime('-10 years'));
//this gives you all upcoming webinars
var_dump($webinars); 
```

If you would like to get the registration/join URL for a webinar you can do so like this:
```php
$webinar = reset($webinars);
$webinar->getRegistrationUrl(); 
```

### Register a user for a webinar
You can really easily register somebody for a webinar. Basically, all you need to do is this:

```php
$consumer = new \Citrix\Entity\Registrant\Post();
$consumer->setFirstName('Alan')->setLastName('Ford'))->setEmail('alan.ford@example.com');

//register a user for the very first upcoming webinar, @see Getting upcoming webinars
$webinar = reset($webinars);

$citrix->register($webinar->getWebinarKey(), $consumer);
```

You can register a user for a webinar by providing the `webinarKey` and the Registrant for POST(ing) data like so:

```php	
$webinarKey = 123123;
$consumer = new \Citrix\Entity\Registrant\Post();
$consumer->setFirstName('Alan')->setLastName('Ford')->setEmail('alan.ford@example.com');

$citrix = new \Citrix\Citrix($auth); 
$citrix->register($webinarKey, $consumer);
```
 ### And many others...
 Just watch at `Citrix.php` methods.
