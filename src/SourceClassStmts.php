<?php

/**
 * SourceClassStmts.phpp – php-compiler
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

use PhpParser\Node\Stmt;

/**
 * The source class stmts class represents an array of stmts that make up a php class.
 */
class SourceClassStmts extends SourceStmts {

	/** @var \PhpParser\Node\Stmt */
	protected $stmts;

	/** @var string */
	protected $namespace;

	/** @var string */
	protected $name;

	/**
	 * Create a new source class stmts instance.
	 *
	 * @param \PhpParser\Node\Stmt $stmt
	 * @param string $namespace
	 * @param string $name
	 */
	public function __construct(Stmt $stmt, string $namespace, string $name) {
		$this->stmts = $stmt;
		$this->namespace = $namespace;
		$this->name = $name;
	}

	/**
	 * Get the namespace.
	 *
	 * @return string
	 */
	public function getNamespace() : string {
		return $this->namespace;
	}

	/**
	 * Get the class/interface/trait name.
	 *
	 * @return string
	 */
	public function getName() : string {
		return $this->name;
	}

}