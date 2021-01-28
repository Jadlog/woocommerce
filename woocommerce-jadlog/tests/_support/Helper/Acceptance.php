<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Acceptance extends \Codeception\Module
{
    function waitForAlert($timeout = 5)
    {
        $this->getModule('WPWebDriver')->webDriver->
            wait($timeout)->
            until(\Facebook\WebDriver\WebDriverExpectedCondition::alertIsPresent());
    }
}
