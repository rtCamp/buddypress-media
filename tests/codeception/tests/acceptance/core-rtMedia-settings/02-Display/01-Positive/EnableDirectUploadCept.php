<?php

/**
 * Scenario : To check direct media upload.
 */
use Page\Login as LoginPage;
use Page\UploadMedia as UploadMediaPage;
use Page\DashboardSettings as DashboardSettingsPage;
use Page\Constants as ConstantsPage;
use Page\BuddypressSettings as BuddypressSettingsPage;

$I = new AcceptanceTester( $scenario );
$I->wantTo( 'To check if the user is allowed to upload the media directly' );

$loginPage = new LoginPage( $I );
$loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

$settings = new DashboardSettingsPage( $I );
$settings->gotoTab( ConstantsPage::$displayTab, ConstantsPage::$displayTabUrl );
$settings->verifyEnableStatus( ConstantsPage::$strDirectUplaodCheckboxLabel, ConstantsPage::$directUploadCheckbox, ConstantsPage::$masonaryCheckbox );

$buddypress = new BuddypressSettingsPage( $I );
$buddypress->gotoMedia( ConstantsPage::$userName );

$uploadmedia = new UploadMediaPage( $I );
$uploadmedia->uploadMediaDirectly( ConstantsPage::$userName, ConstantsPage::$imageName );
?>
