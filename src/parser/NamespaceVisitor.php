<?php

/**
 * NamespaceVisitor.php – php-compiler
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
 * The class used to get the namespace and class name declarations from a file.
 */
class NamespaceVisitor extends AbstractNodeVisitor {

	/** @var string|null */
	protected $currentNamespace = null;

	/** @var array */
	protected $map = [];

	/** @var array */
	protected $globalNamespaceClasses = [];

	/** @var array */
	protected $globalNamespace = [];

	/**
	 * Get the declared class/interface/trait names.
	 *
	 * @return array
	 */
	public function getNamespaceMap() : array {
		return $this->map;
	}

	/**
	 * Get the declared global classes/interfaces/traits.
	 *
	 * @return array
	 */
	public function getGlobalNamespaceClasses() : array {
		return $this->globalNamespaceClasses;
	}

	/**
	 * Get the declared global namespace statements.
	 *
	 * @return array
	 */
	public function getGlobalNamespace() : array {
		return $this->globalNamespace;
	}

	public function enterNode(Node $node) {
		if($node instanceof Node\Stmt\Namespace_) {
			if($node->name !== null) {
				$this->currentNamespace = $node->name->toString();
			}
		} elseif($node instanceof Node\Stmt\Class_ or $node instanceof Node\Stmt\Interface_ or $node instanceof Node\Stmt\Trait_) {
			$this->enterClassLikeNode($node);
			return NodeTraverser::DONT_TRAVERSE_CHILDREN;
		} elseif($this->currentNamespace === null) {
			if($node instanceof Node\Stmt\Declare_) {
				return NodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
			}
		}

		return null;
	}

	public function leaveNode(Node $node) {
			if($node instanceof Node\Stmt\Namespace_) {
				$this->currentNamespace = null;
			}
	}

	/**
	 * Enter a namespace declaration.
	 *
	 * @param \PhpParser\Node\Stmt\Namespace_ $node
	 */
	protected function enterNamespace(Node\Stmt\Namespace_ $node) : void {
		$this->currentNamespace = $node;

		if(!isset($this->map[$node->name->toString()])) {
			$this->map[$node->name->toString()] = [];
		}
	}

	/**
	 * Enter a class like node (class, interface, trait)
	 *
	 * @param \PhpParser\Node\Stmt $node
	 */
	protected function enterClassLikeNode(Node\Stmt $node) {
		/** @var $node Node\Stmt\Class_|Node\Stmt\Interface_|Node\Stmt\Trait_ */
		if($node->name !== null) {
			if($this->currentNamespace !== null) {
				$this->map[$this->currentNamespace][$node->name->toString()] = $node;
			} else {
				$this->globalNamespaceClasses[$node->name->toString()] = $node; //global
			}
		}
	}

}