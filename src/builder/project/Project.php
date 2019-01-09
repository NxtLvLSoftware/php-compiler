<?php

/**
 * Project.php – php-compiler
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

namespace jacknoordhuis\phpcompiler\builder\project;

use jacknoordhuis\phpcompiler\builder\Builder;
use jacknoordhuis\phpcompiler\builder\exception\BuilderException;
use jacknoordhuis\phpcompiler\builder\library\ILibraryHolder;
use jacknoordhuis\phpcompiler\builder\library\LibraryHolder;
use jacknoordhuis\phpcompiler\factory\CompilerFactory;

class Project extends Builder implements ILibraryHolder {
	use LibraryHolder;

	/** @var string|null */
	protected $entry = null;

	/**
	 * Set the project entry file. The build will fail if it is not set.
	 *
	 * @param string $path
	 *
	 * @return \jacknoordhuis\phpcompiler\builder\project\Project
	 *
	 * @throws \jacknoordhuis\phpcompiler\builder\exception\BuilderException
	 */
	public function setEntryFile(string $path) : Project {
		$path = realpath($this->in . DIRECTORY_SEPARATOR . $path);
		if($path !== false and is_file($path)) {
			if(is_readable($path)) {
				$this->entry = $path;
			} else {
				throw new BuilderException("Provided entry file '{$path}' is not readable, make sure the correct permissions are set. Real Path: '{$this->in}" . DIRECTORY_SEPARATOR . "{$path}'");
			}
		} else {
			throw new BuilderException("Provided entry file '{$path}' does not exist or is not a valid file. Real Path: '{$this->in}" . DIRECTORY_SEPARATOR . "{$path}'");
		}

		return $this;
	}

	/**
	 * Build the project.
	 *
	 * @param int|null $count
	 * @param int|null $skipped
	 *
	 * @throws \jacknoordhuis\phpcompiler\exception\CompilerException|\jacknoordhuis\phpcompiler\builder\exception\BuilderException
	 */
	public function build(int &$count = 0, int &$skipped = 0) : void {
		if($this->entry === null) {
			throw new BuilderException("An entry file must be set before the project is built.");
		}

		//setup compiler
		$compiler = CompilerFactory::create();
		$handle = $compiler->prepareOutput($this->out);

		//compiler
		/** @var Builder[] $builtLibs */
		$builtLibs = [];
		$this->buildLibraries($builtLibs, $count, $skipped);

		$compiler->readFromFileList($this->getSourceFiles()
			->addExclusiveFilters($this->getExcludePaths()));

		$compiler->write($handle);

		//write the entry file last
		fwrite($handle, $compiler->getCode($this->entry));
		++$count;
	}

}