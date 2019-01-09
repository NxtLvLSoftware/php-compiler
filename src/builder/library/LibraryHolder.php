<?php

/**
 * RecursiveLibraryHolder.php – php-compiler
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

use jacknoordhuis\phpcompiler\builder\exception\BuilderException;

trait LibraryHolder {

	/** @var \jacknoordhuis\phpcompiler\builder\library\Library[] */
	protected $libraries = [];

	/**
	 * Get all the libraries from the list.
	 *
	 * @return \jacknoordhuis\phpcompiler\builder\library\Library[]
	 */
	public function getLibraries() : array {
		return $this->libraries;
	}

	/**
	 * Add a single library to the list.
	 *
	 * @param \jacknoordhuis\phpcompiler\builder\library\Library $library
	 *
	 * @return \jacknoordhuis\phpcompiler\builder\library\ILibraryHolder|\jacknoordhuis\phpcompiler\builder\library\LibraryHolder
	 */
	public function addLibrary(Library $library) : ILibraryHolder {
		$this->libraries[$library->getName()] = $library;

		return $this;
	}

	/**
	 * Add an array of libraries to the list.
	 *
	 * @param Library[] $libraries
	 *
	 * @return \jacknoordhuis\phpcompiler\builder\library\ILibraryHolder|\jacknoordhuis\phpcompiler\builder\library\LibraryHolder
	 *
	 * @throws \jacknoordhuis\phpcompiler\builder\exception\BuilderException
	 */
	public function addLibraries(array $libraries) : ILibraryHolder {
		foreach($libraries as $library) {
			if($library instanceof Library) {
				$this->libraries[$library->getName()] = $library;
			} else {
				throw new BuilderException("Cannot add library to list due to being type '" . gettype($library) . "' when a " . gettype(Library::class) . " is expected.");
			}
		}

		return $this;
	}

	/**
	 * Recursively build all libraries in the list.
	 *
	 * @param array $built  All built libraries will be added to the array.
	 * @param int $count    The total count of all files built.
	 * @param int $skipped  The total count of all files skipped.
	 *
	 * @throws \jacknoordhuis\phpcompiler\exception\CompilerException
	 */
	public function buildLibraries(array &$built, int &$count, int &$skipped) : void {
		foreach($this->libraries as $library) {
			if(!isset($built[$library->getName()])) {
				$library->build($count, $skipped);
				$built[$library->getName()] = $library;

				if($library instanceof ILibraryHolder) {
					$library->buildLibraries($built, $count, $skipped);
				}
			} else {
				//TODO: Warning about recursive library?
			}
		}
	}

}