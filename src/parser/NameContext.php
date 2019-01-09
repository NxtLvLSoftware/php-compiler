<?php

/**
 * NameContext.php – php-compiler
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

use PhpParser\NameContext as BaseNameContext;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;

class NameContext extends BaseNameContext {

	/**
	 * Start a new namespace.
	 *
	 * @param Name|null $namespace Null is the global namespace
	 */
	public function startNamespace(Name $namespace = null) {
		$this->namespace = $namespace;
	}

	/**
	 * Add an alias / import.
	 *
	 * @param Name   $name        Original name
	 * @param string $aliasName   Aliased name
	 * @param int    $type        One of Stmt\Use_::TYPE_*
	 * @param array  $errorAttrs Attributes to use to report an error
	 * @param bool   $replace    Replace existing aliases?
	 */
	public function addAlias(Name $name, string $aliasName, int $type, array $errorAttrs = [], bool $replace = false) {
		// Constant names are case sensitive, everything else case insensitive
		if($type === Stmt\Use_::TYPE_CONSTANT) {
			$aliasLookupName = $aliasName;
		} else {
			$aliasLookupName = strtolower($aliasName);
		}

		if(!isset($this->aliases[$type][$aliasLookupName]) or $replace) {
			$this->aliases[$type][$aliasLookupName] = $name;
			$this->origAliases[$type][$aliasName] = $name;
		}
	}

}