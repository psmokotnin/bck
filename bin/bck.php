#!/usr/bin/php
<?php
namespace Bck;

require_once(__DIR__ . '/../lib/Autoloader.php');
Autoloader::register();

$cli = new Cli(getcwd(), $argv);
$exitCode = $cli->run();

echo "\nRuntime: ";
printf("%.2f", $cli->getRunTime());
echo "\n";

exit($exitCode);