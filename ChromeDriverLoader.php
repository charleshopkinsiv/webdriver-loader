<?php
///////////////////////////////////////////////////////////////////
//
//  Loads Selenium WebDriver with chrome
//
//  Check chromeversion with chromium-brosers --help
//
//
///////////////////////////////////////////////////////////////////
namespace charleshopkinsiv\WebDriver;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Chrome\ChromeDriver;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;


class ChromeDriverLoader
{

    private static $chromedriver_file = "/home/charles/var/chromedriver";
    private static $default_user_agent = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.81 Safari/537.36";

    public static function load(string $user_agent = "", string $ip_address = "")
    {

        // Check for chromedriver file, if no file, attempt to download and

        putenv('WEBDRIVER_CHROME_DRIVER=' . self::$chromedriver_file);

        $user_agent = empty($user_agent) ? self::$default_user_agent : $user_agent;        
        
        $options = new ChromeOptions();
        $options->addArguments([
            '--headless', 
            'window-size=1024,768',
            '--user-agent=' . $user_agent
        ]);
        $capabilities = DesiredCapabilities::chrome();
        $capabilities->setCapability( ChromeOptions::CAPABILITY, $options );

        return ChromeDriver::start( $capabilities );
    }
}