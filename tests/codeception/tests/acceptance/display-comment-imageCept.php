<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('upload an image and comment on it');

$I->amonPage('/wp-login.php');
$I->fillField( 'input#user_login', 'sumeet' );
$I->fillField( 'input#user_pass', 'sumeet' );
$I-> click('Log In');
$I->amonPage('/activity');
$I->click('#whats-new');
$I->fillfield('#whats-new','Upload test');
$I->attachFile('input[type="file"]','hello.png');
$I->wait(5);
$I->click('Post Update');
$I->wait(10);
//$I->click('Post Update');
$I->wait(2);

// Add a comment
$I->click('.rtmedia-item-thumbnail');
$I->wait(9);
$I->click('textarea#comment_content');
$I->fillField( 'textarea#comment_content', 'This is a test comment by sumeet' );
$I->click('input#rt_media_comment_submit');
$I->wait(4);

?>