<?php

/**
 * AbstractNodeVisitor.php – php-compiler
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

namespace jacknoordhuis\phpcompiler\parser;

use PhpParser\NodeVisitorAbstract;

/**
 * This is the abstract node visitor class.
 *
 * This is used to track the filename.
 */
class AbstractNodeVisitor extends NodeVisitorAbstract {

	/** @var string */
	protected $filename = "";

	/**
	 * Set the full path to the current file being parsed.
	 *
	 * @param string $filename
	 *
	 * @return \jacknoordhuis\phpcompiler\parser\AbstractNodeVisitor
	 */
	public function setFilename(string $filename) : AbstractNodeVisitor {
		$this->filename = $filename;

		return $this;
	}

	/**
	 * Get the full path to the current file being parsed.
	 *
	 * @return string
	 */
	public function getFilename() : string {
		return $this->filename;
	}

	/**
	 * Get the directory of the current file being parsed.
	 *
	 * @return string
	 */
	public function getDir() : string {
		return dirname($this->getFilename());
	}

}