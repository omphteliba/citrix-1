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
-   *Consumer Key*, 
-   *Consumer Secret*

You need the *Consumer Key* for your application to authenticate with Citrix using this library.  

If you want to use the OAuth Authorization, you need to set also a *Callback URL* for your APP.
This *url* is for OAUTH redirection after authentication.  

The principles of operation are explained in [How to Get an Access Token and Organizer Key](https://goto-developer.logmeininc.com/how-get-access-token-and-organizer-key).


Direct Authentication
---------------------
In addition to the *Consumer Key*, for **Direct Authentication** you need your *username* and *password*, 
which is the one that you use to login into [GoToWebinar.com](https://global.gotowebinar.com/webinars.tmpl).

You can setup an authentication adapter like so:

```php	
$auth = new \Citrix\Auth\Direct('CONSUMER_KEY');
$auth->setUsername('USERNAME')->setPassword('PASSWORD');
```

Generally, the things you need the most are the `access_token` and `organizer_key`.  
You can retrieve those like this:

```php	
$auth->applyCredentials();
$accessToken = $auth->getAccessToken(); //returns your access token
$organizerKey = $auth->getOrganizerKey(); //returns the organizer key
```

The code will handle all the authentication stuff for you, so you don't really have to worry about that. 


OAUTH Authentication
--------------------
In order to use the OAUTH Authentication you need to redirect the admin to the authentication form for Citrix platform where he had to digit *username* and *password*.  
Then Citrix will redirect the user to the defined *Callback URL*.  
Citrix will add to the *Callback Url* a param `responseKey` (=*code*) that we need for getting the `access_token` and `organizer_key`.  

```php	
$auth = new \Citrix\Auth\OAuth('CONSUMER_KEY');

// redirect the user to
$redirectUrl = $auth->getAuthorizationLogonUrl();
header("Location: $redirectUrl");
```

In this page the user will insert his credentials and then will be redirected to the *Callback URL*.
Here, your application will grab the `responseKey` (=*code*).
You can retrieve the needed with:

```php
/* @var $auth \Citrix\Auth\OAuth */
$auth = $citrix->getAuth();
// send data to citrix and store 'access_token' and 'organized_key'
// it's important to set the responseKey before applying credentials otherwise will not work
$auth->setResponseKey($responseKey)->applyCredentials();
$accessToken = $auth->getAccessToken(); //returns your access token
$organizerKey = $auth->getOrganizerKey(); //returns the organizer key
```

That is equivalent to:

```bash
curl -X POST -H "Accept:application/json" -H "Content-Type: application/x-www-form-urlencoded" "https://api.getgo.com/oauth/access_token" -d 'grant_type=authorization_code&code={responseKey}&client_id={consumerKey}'
```

### Request Parameters
|Parameter |Description                              |Format|Required|
|:---------|:----------------------------------------|:-----|:-------|
|grant_type|string reading "authorization_code"      |string|required|
|code      |responseKey from the redirect            |string|required|
|client_id |the application client_id or Consumer Key|string|required|


### Response Data Example
This returns an `access_token`, `organizer_key` and user information:

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


Getting upcoming webinars
-------------------------

In order to get all the upcoming webinars, you can write:

```php	
$citrix = new \Citrix\Citrix($auth); 
$webinars = $citrix->getUpcoming();
//this gives you all upcoming webinars
var_dump($webinars); 
```

Creating a webinar
------------------
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

Getting past webinars
---------------------

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

Register a user for a webinar
-----------------------------

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

