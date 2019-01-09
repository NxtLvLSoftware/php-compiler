<?php

/**
 * SourceStmts.php – php-compiler
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

class SourceStmts {

	/** @var \PhpParser\Node[] */
	protected $stmts = null;

	/**
	 * Create a new source stmts instance.
	 *
	 * @param \PhpParser\Node\Stmt[] $stmts
	 */
	public function __construct(array $stmts) {
		$this->stmts = $stmts;
	}

	/**
	 * Get all statement nodes from the source file.
	 *
	 * @return \jacknoordhuis\phpcompiler\SourceStmts[]|\PhpParser\Node\Stmt
	 */
	public function getStmts() {
		return $this->stmts;
	}

}