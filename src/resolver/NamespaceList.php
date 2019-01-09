<?php

/**
 * NamespaceList.php – php-compiler
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

namespace jacknoordhuis\phpcompiler\resolver;

/**
 * The namespace list class maintains a list of all namespaces and it's classes.
 */
class NamespaceList {

	const GLOBAL_NAMESPACE = "global";

	/** @var string[][] */
	protected $namespaces = [];

	/**
	 * Add a namespace to the list.
	 *
	 * @param string $namespace
	 *
	 * @return \jacknoordhuis\phpcompiler\resolver\NamespaceList
	 */
	public function addNamespace(string $namespace) : NamespaceList {
		if(!isset($this->namespaces[$namespace])) {
			$this->namespaces[$namespace] = [];
		}

		return $this;
	}

	/**
	 * Add a class to the list.
	 *
	 * @param string $namespace
	 * @param string $class
	 *
	 * @return \jacknoordhuis\phpcompiler\resolver\NamespaceList
	 */
	public function addClass(string $namespace, string $class) : NamespaceList {
		$this->addNamespace($namespace);
		$this->namespaces[$namespace][] = $class;

		return $this;
	}

	/**
	 * Get all the registered namespaces.
	 *
	 * @return string[][]
	 */
	public function getNamespaces() : array {
		return $this->namespaces;
	}

}