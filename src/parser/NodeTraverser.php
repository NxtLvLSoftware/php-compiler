<?php

/**
 * NodeTraverser.php – php-compiler
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

/**
 * The node traverser class checks all nodes and sets the filename if it is one of our visitors.
 */
class NodeTraverser extends \PhpParser\NodeTraverser {

	/**
	 * Transverse the file.
	 *
	 * @param array  $nodes
	 * @param string $filename
	 *
	 * @return \PhpParser\Node[]
	 */
	public function traverseFile(array $nodes, string $filename) : array {
		// Set the correct state on each visitor
		foreach($this->visitors as $visitor)
			if($visitor instanceof AbstractNodeVisitor) {
				$visitor->setFilename($filename);
		}

		return $this->traverse($nodes);
	}

}