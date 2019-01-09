<?php

/**
 * DirVisitor.php – php-compiler
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

use jacknoordhuis\phpcompiler\parser\exception\DirConstantException;
use PhpParser\Node;
use PhpParser\Node\Scalar\MagicConst\Dir;
use PhpParser\Node\Scalar\String_;

/**
 * The directory node visitor class is used to replace all references to __DIR__ with the actual directory.
 */
class DirVisitor extends AbstractNodeVisitor {

	/** @var bool */
	protected $skip = false;

	/**
	 * Create a new directory visitor instance.
	 *
	 * @param bool $skip
	 */
	public function __construct($skip = false) {
		$this->skip = $skip;
	}

	/**
	 * Enter and modify the node.
	 *
	 * @param \PhpParser\Node $node
	 *
	 * @throws \jacknoordhuis\phpcompiler\parser\exception\DirConstantException
	 *
	 * @return \PhpParser\Node\Scalar\String_|null
	 */
	public function enterNode(Node $node) : ?String_{
		if($node instanceof Dir) {
			if($this->skip) {
				throw new DirConstantException();
			}

			return new String_($this->getDir());
		}

		return null;
	}

}