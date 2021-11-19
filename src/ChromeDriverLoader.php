<?php
///////////////////////////////////////////////////////////////////
//
//  Loads Selenium WebDriver with chrome
//
//  Check chromeversion with chromium-browser --help
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
    private static $driver_url_base = "https://chromedriver.storage.googleapis.com/95.0.4638.69/";
    private static $driver_file_name = "chromedriver_linux64.zip";

    public static function load(string $user_agent = "", string $ip_address = "")
    {

        if(!is_file(__DIR__ . "/../driver_bin/"))
            $this->downloadDriver();


        for($i = 0; $i < 2; $i++) {

            try{

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
            }

            catch(\Exception $e) {


            }
        }

        return ChromeDriver::start( $capabilities );
    }

    public function downloadDriver()
    {

            // Look up chrome version
            exec("chromium-browser --version" , $chrome_version);
            $chrome_version = explode(" ", $chrome_version)[1];

            // Download chromedriver
            file_put_contents("DOWNLOAD.zip", file_get_contents(self::$driver_url_base . $chrome_version . "/" . self::$driver_file_name));

            // Unzip and move file
    }
}