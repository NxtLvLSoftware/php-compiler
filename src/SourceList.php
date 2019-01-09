<?php

/**
 * SourceList.php – php-compiler
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

namespace jacknoordhuis\phpcompiler;

/**
 * The source list class maintains an in-memory copy of all sources in order to compile all
 * duplicate namespaces down into one namespace block.
 */
class SourceList {

	/** @var \jacknoordhuis\phpcompiler\SourceStmts[] */
	protected $sources = [];

	/**
	 * Add a source file to the list.
	 *
	 * @param \jacknoordhuis\phpcompiler\SourceStmts $file
	 *
	 * @return \jacknoordhuis\phpcompiler\SourceList
	 */
	public function addSource(SourceStmts $file) : SourceList {
		$this->sources[] = $file;

		return $this;
	}

	/**
	 * Add an array of sources to the list.
	 *
	 * @param \jacknoordhuis\phpcompiler\SourceStmts[] $files An array of source files.
	 *
	 * @return \jacknoordhuis\phpcompiler\SourceList
	 */
	public function addSources(array $files) : SourceList {
		foreach($files as $file) {
			$this->addSource($file);
		}

		return $this;
	}

	/**
	 * Get all the sources in the list.
	 *
	 * @return \jacknoordhuis\phpcompiler\SourceStmts[]
	 */
	public function getSources() : array {
		return $this->sources;
	}

}