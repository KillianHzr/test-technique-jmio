<?php

use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}

$kernel = new Kernel('test', false);
$kernel->boot();
$application = new Application($kernel);
$application->setAutoExit(false);
$application->run(new ArrayInput(['command' => 'fos:elastica:delete', '--no-interaction' => true]), new NullOutput());
$application->run(new ArrayInput(['command' => 'fos:elastica:create']), new NullOutput());
$kernel->shutdown();
