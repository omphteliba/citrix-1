<?php
/**
 * Created by PhpStorm.
 * User: oliver.hoerold
 * Date: 19.10.2018
 * Time: 10:25
 */

// this file needs to have the same URL as the 'Application URL' of your Citrix App

require_once rex_path::base('vendor/autoload.php');
$citrix_api_key    = 'CONSUMER KEY';
$citrix_api_secret = 'CONSUMER SECRET';


	parse_str($_SERVER['QUERY_STRING'], $query);
	if (array_key_exists('code', $query)) {
		$responseKey = $query['code'];
		$auth        = new \CitrixOAuth2\Auth\OAuth($citrix_api_key, $citrix_api_secret);

		// set the response key and apply for having the 'access_token' and 'organized_key'
		// it's important to set the responseKey before applying credentials otherwise it will not proceed
		$auth->setResponseKey($responseKey);
		try {
			$auth->applyCredentials();
		} catch (Exception $e) {
			echo 'Exception';
			var_dump($e);
			die();
		}

		// finally we get our APP credentials
		$accessToken  = $auth->getAccessToken(); //returns your access token
		$organizerKey = $auth->getOrganizerKey(); //returns the organizer key
		echo 'accesstoken: ' . $accessToken . PHP_EOL;
		echo 'organizerKey: ' . $organizerKey . PHP_EOL;

		$citrix = new \CitrixOAuth2\Citrix($auth);
		//this gives you all upcoming webinars
		$webinars = $citrix->getUpcoming();
		echo 'webinars:' . PHP_EOL;
		echo '<ul>' . PHP_EOL;
		foreach ($webinars as $webinar) {
			echo '<li><a href="' . $webinar->getRegistrationUrl() . '">' . $webinar->getSubject() . '</a> / ' . $webinar->getStartTime()->format('d.m.Y') . '</li>' . PHP_EOL;
		}
		echo '</ul>' . PHP_EOL;
	} else {
	$auth = new \CitrixOAuth2\Auth\OAuth($citrix_api_key);// Get from Citrix the Url where to insert your admin credentials
	try {
		$redirectUrl = $auth->getAuthorizationLogonUrl();// redirect
	} catch (Exception $e) {
		echo '<pre style="margin-top: 100px;">';
		echo 'Exception: Citrix getAuthorizationLogonURL: ' . $e->getMessage();
		var_dump($e);
		die();
	}
	header("Location: $redirectUrl");
}
