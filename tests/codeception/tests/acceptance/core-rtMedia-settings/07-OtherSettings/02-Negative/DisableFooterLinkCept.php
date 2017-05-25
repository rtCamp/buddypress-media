<?php

/**
* Scenario : To check if rtMedia footer link is disabled.
*/

    use Page\Login as LoginPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\Constants as ConstantsPage;

    $scrollToTab = ConstantsPage::$mediaSizesTab;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'To check if rtMedia footer link is disabled.' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoTab( ConstantsPage::$otherSettingsTab, ConstantsPage::$otherSettingsTabUrl, $scrollToTab );
    $settings->verifyDisableStatus( ConstantsPage::$footerLinkLabel, ConstantsPage::$footerLinkCheckbox );
    $I->amOnPage( '/' );
    $I->dontSeeElement( ConstantsPage::$footerLink );
?>
