<?php

/**
 * StrictTypesVisitor.php – php-compiler
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

use PhpParser\Node;

/**
 * The strict types visitor class is used to identify and remove strict type declarations from files.
 */
class StrictTypesVisitor extends AbstractNodeVisitor {

	/**
	 * Enter and modify the node.
	 *
	 * @param \PhpParser\Node $node
	 *
	 * @return int|null;
	 *
	 * @throws \jacknoordhuis\phpcompiler\parser\exception\StrictTypesDeclarationException
	 */
	public function leaveNode(Node $node) {
		if($node instanceof Node\Stmt\Declare_) {
			return NodeTraverser::REMOVE_NODE;
		}

		return null;
	}

}