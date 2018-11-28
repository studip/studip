<?php
// Login requires 2 steps
// 1. step: load front page
$I = new WebGuy($scenario);
$I->wantTo('ensure the standard login works');
$I->amOnPage('/index.php');
$I->see('Login');

// 2. step: continue to login form 
$I->click('Login');
$I->amOnPage('/index.php');
$I->see('Herzlich willkommen');
$I->fillField('loginname', 'test_autor');
$I->fillField('password', 'testing');
$I->click('button[name=Login]');

// test, that we are really logged in and see typical elements
$I->see('Meine Startseite');
