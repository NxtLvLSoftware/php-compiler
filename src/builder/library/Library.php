<?php

/**
 * Library.php – php-compiler
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

namespace jacknoordhuis\phpcompiler\builder\library;

use jacknoordhuis\phpcompiler\builder\Builder;
use jacknoordhuis\phpcompiler\factory\CompilerFactory;

class Library extends Builder implements ILibraryHolder {
	use LibraryHolder;

	/**
	 * Build the library.
	 *
	 * @param int|null $count
	 * @param int|null $skipped
	 *
	 * @throws \jacknoordhuis\phpcompiler\exception\CompilerException
	 */
	public function build(int &$count = 0, int &$skipped = 0) : void {
		//setup compiler
		$compiler = CompilerFactory::create();
		$handle = $compiler->prepareOutput($this->out);

		$compiler->readFromFileList($this->getSourceFiles()
			->addExclusiveFilters($this->getExcludePaths()));

		//compiler
		/** @var Builder[] $builtLibs */
		$builtLibs = [];
		$this->buildLibraries($builtLibs, $count, $skipped);

		$compiler->write($handle);
	}

}