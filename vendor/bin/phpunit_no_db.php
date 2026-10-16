<?php
require __DIR__ . '/../autoload.php';
require __DIR__ . '/../../app/tests/Models/bootstrap.php';
// Run phpunit
$command = new PHPUnit\TextUI\Command;
$command->run($_SERVER['argv'], false);
