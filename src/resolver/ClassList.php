<?php

/**
 * ClassList.php – php-compiler
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
 * The class list class maintains a list of classes using a sort of doubly-linked list.
 */
class ClassList {

	/** @var \jacknoordhuis\phpcompiler\resolver\ClassNode */
	protected $head;

	/** @var \jacknoordhuis\phpcompiler\resolver\ClassNode */
	protected $current;

	/**
	 * Create a new class list instance.
	 */
	public function __construct() {
		$this->clear();
	}

	/**
	 * Clear the contents of the list and reset the head node and current node.
	 */
	public function clear() : void {
		$this->head = new ClassNode();
		$this->current = $this->head;
	}

	/**
	 * Traverse to the next node in the list.
	 */
	public function next() : void {
		if(isset($this->current->next)) {
			$this->current = $this->current->next;
		} else {
			$this->current->next = new ClassNode(null, $this->current);
			$this->current = $this->current->next;
		}
	}

	/**
	 * Insert a value at the current position in the list.
	 *
	 * Any currently set value at this position will be pushed back in the list
	 * after the new value.
	 *
	 * @param mixed $value
	 */
	public function push($value) : void {
		if(!$this->current->value) {
			$this->current->value = $value;
		} else {
			$temp = $this->current;
			$this->current = new ClassNode($value, $temp->prev);
			$this->current->next = $temp;
			$temp->prev = $this->current;
			if($temp === $this->head) {
				$this->head = $this->current;
			} else {
				$this->current->prev->next = $this->current;
			}
		}
	}

	/**
	 * Traverse the ClassList and return a list of classes.
	 *
	 * @return array
	 */
	public function getClasses() : array {
		$classes = [];
		$current = $this->head;
		while($current && $current->value) {
			$classes[] = $current->value;
			$current = $current->next;
		}

		return array_filter($classes);
	}

}