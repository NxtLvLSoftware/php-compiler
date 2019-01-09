<?php

/**
 * FileContainer.php – php-compiler
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

namespace jacknoordhuis\phpcompiler\resolver;

use jacknoordhuis\phpcompiler\exception\CompilerException;

/**
 * The file list class holds an array of file names and filters which are used to filter names when
 * retrieving the list.
 */
class FileList {

	/** @var string[] */
	protected $fileNames = [];

	/** @var array */
	protected $exclusiveFilters = [];

	/** @var array */
	protected $inclusiveFilters = [];

	/**
	 * Construct a new file list instance from an iterator.
	 *
	 * @param \Iterator $iterator
	 *
	 * @return \jacknoordhuis\phpcompiler\resolver\FileList
	 *
	 * @throws \jacknoordhuis\phpcompiler\exception\CompilerException
	 */
	public static function fromIterator(\Iterator $iterator) {
		$list = new self();

		foreach($iterator as $file) {
			if($file instanceof \SplFileInfo) {
				if($file->isFile()) {
					$list->addFile($file->getRealPath());
				}
			} elseif(is_string($file)) {
				$list->addFile($file);
			} else {
				throw new CompilerException("Unsupported iterator value of type '" . gettype($file) . "' passed to jacknoordhuis\\phpcompiler\\resolver::fromIterator(), expected \SplFileInfo or string.");
			}
		}

		return $list;
	}

	/**
	 * Add a single file name to the list.
	 *
	 * @param string $file_name
	 *
	 * @return \jacknoordhuis\phpcompiler\resolver\FileList
	 */
	public function addFile(string $file_name) : FileList {
		$this->fileNames[] = $file_name;

		return $this;
	}

	/**
	 * Add an array of file names to the list.
	 *
	 * @param string[] $file_names
	 *
	 * @return \jacknoordhuis\phpcompiler\resolver\FileList
	 */
	public function addFiles(array $file_names) : FileList {
		$this->fileNames = array_merge($this->fileNames, $file_names);

		return $this;
	}

	/**
	 * Get an array of all file names held by the list that satisfy any added filters.
	 *
	 * @return array
	 */
	public function getFileNames() : array {
		$file_names = [];
		foreach($this->fileNames as $f) {
			foreach($this->inclusiveFilters as $filter)
				if(!preg_match($filter, $f)) {
					continue 2;
			}
			foreach($this->exclusiveFilters as $filter)
				if(preg_match($filter, $f)) {
					continue 2;
			}

			$file_names[] = $f;
		}

		return $file_names;
	}

	/**
	 * Add a filter to exclude matching file names. The files will be
	 * filtered using a regular expression.
	 *
	 * @param string $pattern
	 *
	 * @return \jacknoordhuis\phpcompiler\resolver\FileList
	 */
	public function addExclusiveFilter($pattern) : FileList {
		$this->exclusiveFilters[] = $pattern;

		return $this;
	}

	/**
	 * Add an array of filters to exclude matching file names. The files will be
	 * filtered using a regular expression.
	 *
	 * @param string[] $patterns Array of regular expression patterns.
	 *
	 * @return \jacknoordhuis\phpcompiler\resolver\FileList
	 */
	public function addExclusiveFilters(array $patterns) : FileList {
		$this->exclusiveFilters = array_merge(
			$this->exclusiveFilters,
			$patterns
		);

		return $this;
	}

	/**
	 * Add a filter to only include matching files. The files will be
	 * filtered using a regular expression.
	 *
	 * @param string $pattern Regular expression pattern.
	 *
	 * @return \jacknoordhuis\phpcompiler\resolver\FileList
	 */
	public function addInclusiveFilter(string $pattern) : FileList {
		$this->inclusiveFilters[] = $pattern;

		return $this;
	}

	/**
	 * Add an array of filters to include only matching files. The files will be
	 * filtered using a regular expression.
	 *
	 * @param string[] $patterns Array of regular expression patterns.
	 *
	 * @return \jacknoordhuis\phpcompiler\resolver\FileList
	 */
	public function addInclusiveFilters(array $patterns) : FileList {
		$this->inclusiveFilters = array_merge(
			$this->inclusiveFilters,
			$patterns
		);

		return $this;
	}

}