Citrix API - PHP wrapper around GoToWebinar APIs - 2017
=======================================================

Install via Composer
--------------------

```bash
php composer.phar config repositories.citrix '{"type": "git", "url": "https://github.com/dalpras/citrix.git"}'  
php composer.phar require "dalpras/citrix"
```

Authenticate and Get Going in 15 seconds
----------------------------------------

All you need in order to authenticate with Citrix's API is a consumer key, which you can obtain by registering at [Citrix Developer Center][1]. After registering and adding your application, you will be given *Consumer Key*, 
*Consumer Secret*, and *Callback URL*. You need the *Consumer Key* in order for your application to authenticate with Citrix using this library. 

Direct Authentication
---------------------
In addition to the *Consumer Key*, for **Direct Authentication** you need your *username* and *password*, which is the one that you use to login into [GoToWebinar.com][2].

You can authenticate to Citrix, and your GoToWebinar account respectively, like so:

```php	
$auth = new \Citrix\Auth('CONSUMER_KEY');
$auth->auth('USERNAME', 'PASSWORD'); 
```

Generally, the things you need the most are the `access_token` and `organizer_key`. You can retrieve those like this:

```php	
$auth->getAccessToken(); //returns your access token
$auth->getOrganizerKey(); //returns the organizer key
```

The code will handle all the authentication stuff for you, so you don't really have to worry about that. 

Getting upcoming webinars
-------------------------

In order to get all the upcoming webinars, you have to do this:

```php	
$citrix = new \Citrix\Citrix($auth); //@see $auth definition above 
$webinars = $citrix->getUpcoming();
var_dump($webinars); //this gives you all upcoming webinars
```

Creating a webinar
------------------
In order to create a webinar, you have to do this:
	
```php	
$citrixApi = new \Citrix\CitrixApi($rest, $auth); //@see $auth definition above 
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
$citrix = new \Citrix\Citrix($auth); //@see $auth definition above 
$webinars = $citrix->getPast(new \DateTime('-10 years'));
var_dump($webinars); //this gives you all upcoming webinars
```

If you would like to get the registration/join URL for a webinar you can do so like this:
```php
$webinar = reset($webinars);
$webinar->getRegistrationUrl(); //https://attendee.gotowebinar.com/register/456905497806
```

Register a user for a webinar
-----------------------------

You can really easily register somebody for a webinar. Basically, all you need to do is this:

```php
$consumer = new \Citrix\Entity\Registrant\Post();
$consumer->setFirstName('Joe')->setLastName('Smith'))->setEmail('joe.smith@gmail.com');

//register a user for the very first upcoming webinar, @see Getting upcoming webinars
$webinar = reset($webinars);

$citrix->register($webinar->getWebinarKey(), $consumer);
```

As mentioned above `$auth` you can get from **Authenticate and Get Going in 15 seconds** section, and `$webinar` you can get from  **Getting upcoming webinars** section. 

Alternatively, you can register a user for a webinar by providing the `webinarKey` and the user data directly to the `GoToWebinar` class like so:

```php	
$webinarKey = 123123;
$consumer = new \Citrix\Entity\Registrant\Post();
$consumer->setFirstName('Joe')->setLastName('Smith'))->setEmail('joe.smith@gmail.com');

$citrix = new \Citrix\Citrix($auth); 
$citrix->register($webinarKey, $consumer);
```

Error handling
--------------

The code does handle errors but it fails silently. You can check for errors like so:

```php
$consumer = new \Citrix\Entity\Registrant\Post();
$consumer->setFirstName('Joe')->setLastName('Smith'))->setEmail('joe.smith@gmail.com');

//register a user for the very first upcoming webinar, @see Getting upcoming webinars
$webinar = reset($webinars);

$citrix = new \Citrix\Citrix($auth); 
$citrix->register($webinarKey, $consumer);

if($registration->hasErrors()){
   //get the first error that occurred and use it as the exception message
   throw new \Exception($registration->getError());
}
   
//no errors, continue...
die('Registration was successful.');
```

