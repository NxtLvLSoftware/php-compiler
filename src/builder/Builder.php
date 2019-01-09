<?php

/**
 * Builder.php – php-compiler
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

namespace jacknoordhuis\phpcompiler\builder;

use jacknoordhuis\phpcompiler\builder\exception\BuilderException;
use jacknoordhuis\phpcompiler\resolver\FileList;

abstract class Builder {

	/** @var string */
	protected $name;

	/** @var string */
	protected $in;

	/** @var string */
	protected $out;

	/** @var string[] */
	protected $excludePaths = [];

	/** @var \jacknoordhuis\phpcompiler\builder\Builder[] */
	protected $builders = [];

	/**
	 * Compiler a new builder instance.
	 *
	 * @param string $name
	 * @param string $in
	 * @param string $out
	 * @param array $exclude_paths
	 *
	 * @throws \jacknoordhuis\phpcompiler\builder\exception\BuilderException
	 */
	public function __construct(string $name, string $in, string $out, array $exclude_paths = []) {
		$this->name = $name;
		$this
			->setSourcePath($in)
			->setOutputFile($out)
			->addExcludePaths($exclude_paths);
	}

	/**
	 * Build the project or library.
	 *
	 * @param int|null $count    Will be set to the number of files successfully compiled.
	 * @param int|null $skipped  Will be set to the number of files skipped (usually due to errors).
	 */
	abstract public function build(int &$count = 0, int &$skipped = 0) : void;

	/**
	 * Get the project/library name.
	 *
	 * @return string
	 */
	public function getName() : string {
		return $this->name;
	}

	/**
	 * Get the sources absolute base directory.
	 *
	 * @return string
	 */
	public function getSourcePath() : string {
		return $this->in;
	}

	/**
	 * Get the absolute output file path.
	 *
	 * @return string
	 */
	public function getOutputFile() : string {
		return $this->out;
	}

	/**
	 * Get the array of source-relative exclude paths.
	 *
	 * @return string[]
	 */
	public function getExcludePaths() : array {
		return $this->excludePaths;
	}

	/**
	 * Add a source-directory relative path to exclude from the compiler.
	 *
	 * @param string $path
	 *
	 * @return \jacknoordhuis\phpcompiler\builder\Builder
	 *
	 * @throws \jacknoordhuis\phpcompiler\builder\exception\BuilderException
	 */
	public function addExcludePath(string $path) : Builder {
		$new = realpath($this->in . DIRECTORY_SEPARATOR . $path);
		if($new !== false) {
			$this->excludePaths[] = "/" . str_replace("/", "\\/", preg_quote($new)) . "/"; //build a regular expression for matching the exclude path
		} else {
			throw new BuilderException("Could not determine real path of partial exclude path! Root path: '{$this->in}', Partial Path: '{$path}', Real Path: '{$this->in}" . DIRECTORY_SEPARATOR . "{$path}'");
		}

		return $this;
	}

	/**
	 * Add an array of source-directory relative paths to exclude from the compiler.
	 *
	 * @param string[] $paths
	 *
	 * @return \jacknoordhuis\phpcompiler\builder\Builder
	 *
	 * @throws \jacknoordhuis\phpcompiler\builder\exception\BuilderException
	 */
	public function addExcludePaths(array $paths) : Builder {
		foreach($paths as $path) {
			$this->addExcludePath($path);
		}

		return $this;
	}

	/**
	 * Get a file list of absolute paths to all .php files in the source directory.
	 *
	 * @return \jacknoordhuis\phpcompiler\resolver\FileList
	 *
	 * @throws \jacknoordhuis\phpcompiler\exception\CompilerException
	 */
	protected function getSourceFiles() : FileList {
		return FileList::fromIterator(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->in)))
			->addInclusiveFilter('/^.+\.php$/i');
	}

	/**
	 * An internal method used to verify the source path is usable before setting it.
	 *
	 * @param string $path
	 *
	 * @return \jacknoordhuis\phpcompiler\builder\Builder
	 *
	 * @throws \jacknoordhuis\phpcompiler\builder\exception\BuilderException
	 */
	protected function setSourcePath(string $path) : Builder {
		/** @var string|bool $path */
		$path = realpath($path);
		if($path !== false and is_dir($path)) {
			if(is_readable($path)) {
				$this->in = $path;
			} else {
				throw new BuilderException("Provided path '{$path}' is not readable, make sure the correct permissions are set.");
			}
		} else {
			throw new BuilderException("Provided path '{$path}' does not exist or is not a valid directory.");
		}

		return $this;
	}

	/**
	 * An internal method used to verify an output file is usable before setting it.
	 *
	 * @param string $path
	 *
	 * @return \jacknoordhuis\phpcompiler\builder\Builder
	 *
	 * @throws \jacknoordhuis\phpcompiler\builder\exception\BuilderException
	 */
	protected function setOutputFile(string $path) : Builder {
		$dir = realpath(dirname($path));
		if($dir === false) { //the directory may not exist
			$dir = dirname($path);
			if(!is_dir($dir)) {
				if(!mkdir($dir, 0777, true)) {
					throw new BuilderException("Unable to create output directory '{$dir}'.");
				}
			} else {
				throw new BuilderException("Provided path '{$path}' does not exist or is not a valid directory.");
			}
		}

		$this->out = $path;

		return $this;
	}

}