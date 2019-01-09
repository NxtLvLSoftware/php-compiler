<?php

/**
 * NameResolver.php – php-compiler
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

use PhpParser\ErrorHandler;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;

class NameResolver extends AbstractNodeVisitor {

	/** @var \jacknoordhuis\phpcompiler\parser\NameContext Naming context */
	protected $nameContext;

	/** @var bool */
	protected $replace = true;

	public function __construct(?ErrorHandler $errorHandler = null) {
		$this->nameContext = new NameContext($errorHandler ?? new ErrorHandler\Throwing);
	}

	/**
	 * Start replacing resolved names.
	 */
	public function enableReplace() : void {
		$this->replace = true;
	}

	/**
	 * Stop replacing resolved names.
	 */
	public function disableReplace() : void {
		$this->replace = false;
	}

	/**
	 * Get name resolution context.
	 *
	 * @return NameContext
	 */
	public function getNameContext() : NameContext {
		return $this->nameContext;
	}

	public function beforeTraverse(array $nodes) {
		$this->nameContext->startNamespace();
		return null;
	}

	public function enterNode(Node $node) {
		$this->checkNodeAdd($node);

		if($this->replace) {
			$this->checkNodeReplace($node);
		}

		return null;
	}

	private function addAlias(Stmt\UseUse $use, $type, Name $prefix = null) {
		// Add prefix for group uses
		$name = $prefix ? Name::concat($prefix, $use->name) : $use->name;
		// Type is determined either by individual element or whole use declaration
		$type |= $use->type;

		$this->nameContext->addAlias(
			$name, (string) $use->getAlias(), $type, $use->getAttributes()
		);
	}

	/** @param Stmt\Function_|Stmt\ClassMethod|Expr\Closure $node */
	private function resolveSignature($node) {
		foreach($node->params as $param) {
			$param->type = $this->resolveType($param->type);
		}
		$node->returnType = $this->resolveType($node->returnType);
	}

	private function resolveType($node) {
		if($node instanceof Node\NullableType) {
			$node->type = $this->resolveType($node->type);
			return $node;
		}
		if($node instanceof Name) {
			return $this->resolveClassName($node);
		}
		return $node;
	}

	/**
	 * Resolve name, according to name resolver options.
	 *
	 * @param Name $name Function or constant name to resolve
	 * @param int  $type One of Stmt\Use_::TYPE_*
	 *
	 * @return Name Resolved name, or original name with attribute
	 */
	protected function resolveName(Name $name, int $type) : Name {
		$resolvedName = $this->nameContext->getResolvedName($name, $type);
		if($resolvedName !== null) {
			return $resolvedName;
		}

		// unqualified names inside a namespace cannot be resolved at compile-time
		// add the namespaced version of the name as an attribute
		$name->setAttribute('namespacedName', Name\FullyQualified::concat(
			$this->nameContext->getNamespace(), $name, $name->getAttributes()));
		return $name;
	}

	protected function resolveClassName(Name $name) {
		return $this->resolveName($name, Stmt\Use_::TYPE_NORMAL);
	}

	protected function addNamespacedName(Node $node) {
		$node->namespacedName = Name::concat(
			$this->nameContext->getNamespace(), (string) $node->name);
	}

	/**
	 * Check nodes for valid namespaces.
	 *
	 * @param \PhpParser\Node $node
	 */
	protected function checkNodeAdd(Node $node) : void {
		if($node instanceof Stmt\Namespace_) {
			$this->nameContext->startNamespace($node->name);
		} elseif($node instanceof Stmt\Use_) {
			foreach($node->uses as $use) {
				$this->addAlias($use, $node->type, null);
			}
		} elseif($node instanceof Stmt\GroupUse) {
			foreach($node->uses as $use) {
				$this->addAlias($use, $node->type, $node->prefix);
			}
		} elseif($node instanceof Stmt\Class_) {
			if($node->name !== null) {
				$this->addNamespacedName($node);
			}
		} elseif($node instanceof Stmt\Trait_) {
			$this->addNamespacedName($node);
		} elseif($node instanceof Stmt\Function_) {
			$this->addNamespacedName($node);
		} elseif($node instanceof Stmt\Const_) {
			foreach($node->consts as $const) {
				$this->addNamespacedName($const);
			}
		}
	}

	/**
	 * Attempt to replace valid nodes.
	 *
	 * @param \PhpParser\Node $node
	 */
	protected function checkNodeReplace(Node $node) : void {
		if($node instanceof Stmt\Class_) {
			if($node->extends !== null) {
				$node->extends = $this->resolveClassName($node->extends);
			}

			foreach($node->implements as &$interface) {
				$interface = $this->resolveClassName($interface);
			}
		} elseif($node instanceof Stmt\Interface_) {
			foreach($node->extends as &$interface) {
				$interface = $this->resolveClassName($interface);
			}
		}elseif($node instanceof Stmt\Function_) {
			$this->resolveSignature($node);
		} elseif($node instanceof Stmt\ClassMethod or $node instanceof Expr\Closure) {
			$this->resolveSignature($node);
		} elseif($node instanceof Expr\StaticCall or $node instanceof Expr\StaticPropertyFetch or $node instanceof Expr\ClassConstFetch or $node instanceof Expr\New_ or $node instanceof Expr\Instanceof_) {
			if($node->class instanceof Name) {
				$node->class = $this->resolveClassName($node->class);
			}
		} elseif($node instanceof Stmt\Catch_) {
			foreach($node->types as &$type) {
				$type = $this->resolveClassName($type);
			}
		} elseif($node instanceof Expr\FuncCall) {
			if($node->name instanceof Name) {
				$node->name = $this->resolveName($node->name, Stmt\Use_::TYPE_FUNCTION);
			}
		} elseif($node instanceof Expr\ConstFetch) {
			$node->name = $this->resolveName($node->name, Stmt\Use_::TYPE_CONSTANT);
		} elseif($node instanceof Stmt\TraitUse) {
			foreach($node->traits as &$trait) {
				$trait = $this->resolveClassName($trait);
			}

			foreach($node->adaptations as $adaptation) {
				if($adaptation->trait !== null) {
					$adaptation->trait = $this->resolveClassName($adaptation->trait);
				}

				if($adaptation instanceof Stmt\TraitUseAdaptation\Precedence) {
					foreach($adaptation->insteadof as &$insteadof) {
						$insteadof = $this->resolveClassName($insteadof);
					}
				}
			}
		}
	}

}