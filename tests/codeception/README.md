# README #


## What is this repository for?

This repository will contain automated test cases for rtMedia using codeception. Currently using http://codeception.com/for/wordpress wpcept package.

## Current Set up

Tests are in codeception branch under codeception directory. rtMedia > tests > codeception

Under codeception directory there's the composer.json file.


## How to set up?

### Install composer globally

Composer needs to be installed. 

### At codeception directory run composer update

Clone this repo 

Change branch to codeception

Navigate to rtMedia > tests > codeception directory and run `composer update`

This will install all dependencies in your local setup.


### Update .yml files as per requirements

Update acceptance.suite.yml and other yml files as per requirements.

## How to run tests

Navigate to codeception directory and run

`wpcept run acceptance exampleCept.php`

## To test local sites using browserstack

Need to enable local Testing to test local development server.

Download the appropriate binary. Unzip it and run it with your key

`./BrowserStackLocal --key yourkey`

http://codeception.com/docs/modules/WebDriver#BrowserStack

https://www.browserstack.com/local-testing#command-line

#### Make sure selenium server is running

`java -jar selenium-server-standalone-3.4.0.jar`


**Note:** If vendor/bin is not added to path, then you need to run

`vendor/bin/wpcept run acceptance exampleCept.php`