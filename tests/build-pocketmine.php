<?php

/**
 * build-pocketmine.php – php-compiler
 *
 * Copyright (C) 2018 Jack Noordhuis
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author Jack
 *
 */

declare(strict_types=1);

require_once __DIR__ . "/../vendor/autoload.php";

if(!isset($argv[1])) {
	echo "You must specify the path to a PocketMine-MP source installation." . PHP_EOL;
	exit;
}

$source = realpath($argv[1]);
if($source === false or !is_dir($source)) {
	echo "You must specify a valid path to a PocketMine-MP source installation." . PHP_EOL;
	exit;
}

if(!is_dir($source . DIRECTORY_SEPARATOR . "vendor") or !is_dir($source . DIRECTORY_SEPARATOR . "src")) {
	echo "You must specify the path a valid PocketMine-MP source installation. Make sure to run 'composer install' after cloning from git." . PHP_EOL;
	exit;
}

echo "[PHP-Compiler] Building PocketMine-MP project using sources from '{$source}'...". PHP_EOL;

$project = new \jacknoordhuis\phpcompiler\builder\project\Project("PocketMine", $source . "/src", __DIR__ . "/../build/pocketmine/pocketmine.php", ["pocketmine/PocketMine.php"]);
$project->setEntryFile("pocketmine/PocketMine.php");
$project->addLibraries([
		new \jacknoordhuis\phpcompiler\builder\library\Library("BinaryUtils", $source . "/vendor/pocketmine/binaryutils", __DIR__ . "/../build/pocketmine/binaryutils.php"),
		new \jacknoordhuis\phpcompiler\builder\library\Library("Math", $source . "/vendor/pocketmine/math", __DIR__ . "/../build/pocketmine/math.php"),
		new \jacknoordhuis\phpcompiler\builder\library\Library("NBT", $source . "/vendor/pocketmine/nbt", __DIR__ . "/../build/pocketmine/nbt.php"),
		new \jacknoordhuis\phpcompiler\builder\library\Library("Snooze", $source . "/vendor/pocketmine/snooze", __DIR__ . "/../build/pocketmine/snooze.php"),
		new \jacknoordhuis\phpcompiler\builder\library\Library("SPL", $source . "/vendor/pocketmine/spl", __DIR__ . "/../build/pocketmine/spl.php", ["stubs"]),
]);

$start = microtime(true);

$count = $skipped = 0;
$project->build($count, $skipped);

echo "[{$project->getName()}] Compiled " . count($project->getLibraries()) . " libraries with {$count} total files (skipped {$skipped}) in " . round(microtime(true) - $start, 3) . "s!" . PHP_EOL;