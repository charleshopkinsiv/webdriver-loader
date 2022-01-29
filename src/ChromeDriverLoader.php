<?php

namespace WebDriverLoader;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Chrome\ChromeDriver;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;


class ChromeDriverLoader
{

    private static $chromedriver_dir = __DIR__ . "/../bin";
    private static $default_user_agent = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.81 Safari/537.36";
    private static $driver_url_base = "https://chromedriver.storage.googleapis.com/";
    private static $driver_file_name = "chromedriver_linux64.zip";
    private static $zip_file = __DIR__ . "/DOWNLOAD.zip";

    public static function load(string $user_agent = "", string $ip_address = "")
    {

        if(!is_file(self::$chromedriver_dir . "/chromedriver"))
            self::downloadBin();

        for($i = 0; $i < 2; $i++) {

            try{

                putenv('WEBDRIVER_CHROME_DRIVER=' . self::$chromedriver_dir . "/chromedriver");

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

                if(empty($already_downloaded)) {

                    self::downloadBin();
                    $already_downloaded = true;
                }
            }
        }

        return ChromeDriver::start( $capabilities );
    }

    public static function downloadBin()
    {

            // Look up chrome version
            exec("chromium-browser --version" , $chrome_version);
            $chrome_version = explode(" ", $chrome_version[0])[1];

            // Download chromedriver
            file_put_contents(self::$zip_file, file_get_contents(self::$driver_url_base . $chrome_version . "/" . self::$driver_file_name));

            // Unzip and move file
            $Zip = new \ZipArchive;
            if($file = $Zip->open(self::$zip_file)) {

                $Zip->extractTo(self::$chromedriver_dir);
                $Zip->close();
                unlink(self::$zip_file);
                chmod(self::$chromedriver_dir . "/chromedriver", 0775);
            }

            else {

                throw new \Exception("Error unzipping file.");
            }
    }
}