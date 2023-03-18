<?php

namespace WebDriverLoader;

use Facebook\WebDriver\Remote\{DesiredCapabilities,RemoteWebDriver,WebDriverCapabilityType};
use Facebook\WebDriver\Chrome\{ChromeDriver,ChromeOptions,ChromeDriverService};
use Facebook\WebDriver\{WebDriverBy,WebDriverExpectedCondition};


class ChromeDriverLoader
{

    private static $max_instances   = 10;
    private static $port            = 4444;
    private static $dont_try_again  = false;

    private static $chromedriver_dir = __DIR__ . "/../bin";
    private static $default_user_agent = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.81 Safari/537.36";
    private static $driver_url_base = "https://chromedriver.storage.googleapis.com/";
    private static $zip_file = __DIR__ . "/DOWNLOAD.zip";

    private static RemoteWebDriver $instance;

    public static function instance(string $user_agent = "", string $ip_address = "") : RemoteWebDriver
    {

        if(!isset(self::$instance)) {

            self::$instance = self::load($user_agent, $ip_address);
        }

        return self::$instance;
    }

    public static function load(string $user_agent = "", string $ip_address = "")
    {

        if(PHP_OS_FAMILY != 'Linux') {

            throw new \Exception('ChromeDriverLoader only supports Linux OS\'s');
        }

        try{
            printf("Starting chromedriver\n");
            self::startChromeDriver();
            self::setIp($ip_address);  
            $options = new ChromeOptions();
            $options->addArguments([
                '--headless', 
                '--remote-debugging-port=9222',
                'window-size=1024,768',
                '--user-agent=' . empty($user_agent) ? self::$default_user_agent : $user_agent,
            ]);
            $capabilities = DesiredCapabilities::chrome();
            $capabilities->setCapability(ChromeOptions::CAPABILITY, $options);
            printf("Launching chromedriver\n");
            return RemoteWebDriver::create('http://localhost:' . self::$port, $capabilities);
        }
        catch(\Exception $e) { // Delete, download and retry

            printf("CD Error: %s\n", $e->getTraceAsString());
            if(self::$dont_try_again) {

                throw new \Exception('Unknown webdriver loader error');
            }

            if(file_exists(self::$chromedriver_dir . "/chromedriver")) {

                unlink(self::$chromedriver_dir . "/chromedriver");
            }
            self::downloadBin();
            self::$dont_try_again = true;
            self::load($user_agent, $ip_address);
        }
        // catch(\Exception $e) {

        //     printf("CD Error%s\n", $e->getTraceAsString());
        // }
    }

    private static function startChromeDriver()
    {

        if(!is_file(self::$chromedriver_dir . "/chromedriver"))
            self::downloadBin();

        $instances = array_filter(explode("\n", shell_exec('ps -aux | grep chromedriver')));
        $running = (count($instances) > 2) ? true : false;
        if(!$running) {

            exec(self::$chromedriver_dir . '/chromedriver --port=' . self::$port . ' > /dev/null 2>&1 &');
        }
    }

    private static function setIp(string $ip_address)
    {

        putenv("https_proxy=" . $ip_address);
        putenv("http_proxy="  . $ip_address);
        putenv("no_proxy=localhost,127.0.0.1");
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
        else {

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
