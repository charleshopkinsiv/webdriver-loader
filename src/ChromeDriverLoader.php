<?php

namespace WebDriverLoader;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use Facebook\WebDriver\Chrome\ChromeDriver;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;


class ChromeDriverLoader
{

    private static $max_instances = 10;

    private static $chromedriver_dir = __DIR__ . "/../bin";
    private static $default_user_agent = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.81 Safari/537.36";
    private static $driver_url_base = "https://chromedriver.storage.googleapis.com/";
    private static $zip_file = __DIR__ . "/DOWNLOAD.zip";

    public static function load(string $user_agent = "", string $ip_address = "")
    {

        if(!is_file(self::$chromedriver_dir . "/chromedriver"))
            self::downloadBin();

        for($i = 0; $i < 2; $i++) {

            try{

                $user_agent = empty($user_agent) ? self::$default_user_agent : $user_agent;     


                //
                //  Single Way
                //
                putenv('WEBDRIVER_CHROME_DRIVER=' . self::$chromedriver_dir . "/chromedriver");   
                $options = new ChromeOptions();
                $options->addArguments([
                    '--headless', 
                    '--remote-debugging-port=9222',
                    'window-size=1024,768',
                    '--user-agent=' . $user_agent,
                ]);
                $capabilities = DesiredCapabilities::chrome();
                $capabilities->setCapability(ChromeOptions::CAPABILITY, $options);


                //  
                //  Bulk Way
                //


                //  Check how many chromedriver instances are running
                // count(explode('/n', exec('ps -aux | grep chromedriver'))) - 2;


                //  Delete any that are inactive past minute


                //  Start new chromedriver instance if less than 10 running
                //      From port 95[15-24]



                if(PHP_OS_FAMILY == 'Linux') {

                    putenv("https_proxy=" . $ip_address);
                    putenv("http_proxy=" . $ip_address);
                    putenv("no_proxy=localhost,127.0.0.1");
                }
                elseif(PHP_OS_FAMILY == 'Windows') {
                } else {
    
                    throw new \Exception('Only supports Windows and Linux OS\'s');
                }

                break;
            }

            catch(\Exception $e) {


                printf($e . "\n");

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
            $chrome_parent_version = explode(".", $chrome_version)[0];

            // If... Delete and redownload
            // Facebook\WebDriver\Exception\SessionNotCreatedException


            // Find most recent file
            $newest_version = file_get_contents("https://chromedriver.storage.googleapis.com/LATEST_RELEASE_" . $chrome_parent_version);


            // Download most recent file for OS
            if(PHP_OS_FAMILY == 'Linux') {

                file_put_contents(self::$zip_file, file_get_contents(self::$driver_url_base . $newest_version . "/chromedriver_linux64.zip"));
            }
            elseif(PHP_OS_FAMILY == 'Windows') {
            } else {

                throw new \Exception('Only supports Windows and Linux OS\'s');
            }


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
