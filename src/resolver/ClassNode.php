<?php

/**
 * ClassNode.php – php-compiler
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
 * The class node class contains a value, and the previous/next pointers.
 */
class ClassNode {

	/** @var \jacknoordhuis\phpcompiler\resolver\ClassNode|null */
	public $next;

	/** @var \jacknoordhuis\phpcompiler\resolver\ClassNode|null */
	public $prev;

	/** @var mixed */
	public $value;

	/**
	 * Create a new class node instance.
	 *
	 * @param mixed                                              $value
	 * @param \jacknoordhuis\phpcompiler\resolver\ClassNode|null $prev
	 */
	public function __construct($value = null, ClassNode $prev = null) {
		$this->value = $value;
		$this->prev = $prev;
	}

}