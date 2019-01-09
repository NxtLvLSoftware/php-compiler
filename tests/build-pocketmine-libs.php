<?php

/**
 * build-pocketmine-libs.php – phpcompiler
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

/**
 * Simple test script for compiling the PocketMine-MP server software. We use this as an example/test case due to it's
 * large, complex codebase and as it is a console application.
 */

require_once __DIR__ . "/../vendor/autoload.php";

/** @var \jacknoordhuis\phpcompiler\builder\Builder[] $libs */
$libs = [
	new \jacknoordhuis\phpcompiler\builder\library\Library("BinaryUtils", "/Users/Jack/Documents/PocketMine-MP/vendor/pocketmine/binaryutils", __DIR__ . "/../build/pocketmine/binaryutils.php"),
	new \jacknoordhuis\phpcompiler\builder\library\Library("Math", "/Users/Jack/Documents/PocketMine-MP/vendor/pocketmine/math", __DIR__ . "/../build/pocketmine/math.php"),
	new \jacknoordhuis\phpcompiler\builder\library\Library("NBT", "/Users/Jack/Documents/PocketMine-MP/vendor/pocketmine/nbt", __DIR__ . "/../build/pocketmine/nbt.php"),
	new \jacknoordhuis\phpcompiler\builder\library\Library("Snooze", "/Users/Jack/Documents/PocketMine-MP/vendor/pocketmine/snooze", __DIR__ . "/../build/pocketmine/snooze.php"),
	new \jacknoordhuis\phpcompiler\builder\library\Library("SPL", "/Users/Jack/Documents/PocketMine-MP/vendor/pocketmine/spl", __DIR__ . "/../build/pocketmine/spl.php", ["stubs"]),
];

$total_start = microtime(true);

$total_count = $total_skipped = 0;

foreach($libs as $lib) {
	$start = microtime(true);

	$count = $skipped = 0;
	$lib->build($count, $skipped);

	echo "[{$lib->getName()}] Compiled {$count} files (skipped {$skipped}) in " . round(microtime(true) - $start, 3) . "s!" . PHP_EOL;

	$total_count += $count;
	$total_skipped += $skipped;
}

echo "Compiled " . count($libs) . " libraries with {$total_count} files (skipped {$total_skipped}) in " . round(microtime(true) - $total_start, 3) . "s!" . PHP_EOL;

