<?php

namespace Page;

class Login {

	public static $wpUserNameField = 'input#user_login';
	public static $wpPasswordField = 'input#user_pass';
	public static $wpSubmitButton = 'input#wp-submit';
	public static $loginLink = 'li#wp-admin-bar-bp-login';
	public static $dashBoardMenu = 'li#menu-dashboard';

	public static function route( $param ) {
		return static::$URL . $param;
	}

	protected $tester;

	public function __construct( \AcceptanceTester $I ) {
		$this->tester = $I;
	}

	public function loginAsAdmin( $wpUserName, $wpPassword, $saveSession = true ) {
		$I = $this->tester;

		$I->amOnPage( '/wp-admin' );

		// Will load the session saved in saveSessionSnapshot().
		if ( $I->loadSessionSnapshot( 'login' ) ) {
			// $I->reloadPage();
			echo "skipping login steps";
			return;
		}

		if ( ! $saveSession ) {
			$I->waitForElement( self::$wpSubmitButton, 10 );
		}

		$I->seeElement( self::$wpUserNameField );
		$I->fillfield( self::$wpUserNameField, $wpUserName );

		$I->seeElement( self::$wpPasswordField );
		$I->fillfield( self::$wpPasswordField, $wpPassword );

		// $I->seeElement( self::$wpSubmitButton );
		$I->click( self::$wpSubmitButton );
		$I->waitForElement( self::$dashBoardMenu, 10 );

		if ( $saveSession ) {
			$I->saveSessionSnapshot( 'login' ); //Saving session
			echo "Session saved!";
		} else {
			echo "Session not saved!";
		}
		$I->reloadPage();
		// $I->seeElement( self::$dashBoardMenu );

		$I->maximizeWindow();
	}

}
