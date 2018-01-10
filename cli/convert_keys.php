<?php

namespace Nekudo\ShinyDeploy\Cli;

use Apix\Log\Logger;
use Noodlehaus\Config;
use ShinyDeploy\Domain\Database\Auth;
use ShinyDeploy\Exceptions\MissingDataException;

class KeyConverter
{
    private $config;

    private $logger;

    public function __construct(Config $config, Logger $logger)
    {
        $this->config = $config;
        $this->logger = $logger;
    }

    public function __invoke()
    {
        try {
            $this->checkSapi();
            $this->checkPhpVersion();
            $this->checkMcryptExtension();

            $password = $this->readSystemPassword();
            $this->validateSystemPassword($password);

            // @todo Decrypt encryption key with old/deprecated encryption method

            // @todo Encrypt encryption key with new encryption method


        } catch (\Exception $e) {
            echo 'Error: ' . $e->getMessage() . PHP_EOL;
            echo 'File: ' . $e->getFile() . ':' . $e->getLine() . PHP_EOL;
            echo PHP_EOL;
        }
    }

    /**
     * Checks if PHP runs in cli mode.
     *
     * @return void
     * @throws \RuntimeException
     */
    private function checkSapi() : void
    {
        if (php_sapi_name() !== 'cli') {
            throw new \RuntimeException('This script can only be executed in cli mode.');
        }
    }

    /**
     * Checks if script runs with correct PHP version.
     *
     * @return void
     * @throws \RuntimeException
     */
    private function checkPhpVersion() : void
    {
        if (version_compare(PHP_VERSION, '7.1.0', '>=') === false) {
            throw new \RuntimeException('PHP Version has to be 7.1.*');
        }
        if (version_compare(PHP_VERSION, '7.2.0', '<') === false) {
            throw new \RuntimeException('PHP Version has to be 7.1.*');
        }
    }

    /**
     * Check if mcrypt extension is loaded.
     *
     * @return void
     * @throws \RuntimeException
     */
    private function checkMcryptExtension() : void
    {
        if (extension_loaded('mcrypt') === false) {
            throw new \RuntimeException('PHP extension mcrypt is required.');
        }
    }

    /**
     * Reads system password from terminal.
     *
     * @return string
     */
    private function readSystemPassword() : string
    {
        fwrite(STDOUT, "Please enter your system password: ");
        $oldStyle = shell_exec('stty -g');
        shell_exec('stty -echo');
        $password = rtrim(fgets(STDIN), "\n");
        shell_exec('stty ' . $oldStyle);
        echo PHP_EOL;
        return $password;
    }

    /**
     * Checks if system password is correct.
     *
     * @param string $password
     * @throws MissingDataException
     */
    private function validateSystemPassword(string $password) : void
    {
        $authDomain = new Auth($this->config, $this->logger);
        $hashFromDatabase = $authDomain->getPasswordHashByUsername('system');
        $hashFromPassword = hash('sha256', $password);
        if ($hashFromPassword !== $hashFromDatabase) {
            throw new \RuntimeException('System password is invalid.');
        }
    }
}

require_once __DIR__ . '/bootstrap.php';
$converter = new KeyConverter($config, $logger);
$converter->__invoke();
