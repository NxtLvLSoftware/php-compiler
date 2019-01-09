<?php

/**
 * ClassLoader.php – php-compiler
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
 * The class loader class creates an autoloader that intercepts and keeps track of each include
 * in order that files must be included. This autoloader proxies to all other underlying autoloaders.
 */
class ClassLoader {

	/** @var \jacknoordhuis\phpcompiler\resolver\ClassList */
	public $classList;

	/**
	 * Create a new class loader instance.
	 */
	public function __construct() {
		$this->classList = new ClassList();
	}

	/**
	 * Destroy the class loader.
	 *
	 * This makes sure we're unregistered from the autoloader.
	 */
	public function __destruct() {
		$this->unregister();
	}

	/**
	 * Wrap a block of code in the autoloader and get a list of loaded classes.
	 *
	 * @param callable $func
	 *
	 * @return \jacknoordhuis\phpcompiler\resolver\FileList
	 */
	public static function getIncludes(callable $func) : FileList {
		$loader = new static();
		call_user_func($func, $loader);
		$loader->unregister();

		$config = new FileList();
		foreach($loader->getFileNames() as $file) {
			$config->addFile($file);
		}

		return $config;
	}

	/**
	 * Registers this instance as an autoloader.
	 *
	 * @return void
	 */
	public function register() : void {
		spl_autoload_register([$this, 'loadClass'], true, true);
	}

	/**
	 * Unregisters this instance as an autoloader.
	 *
	 * @return void
	 */
	public function unregister() : void {
		spl_autoload_unregister([$this, 'loadClass']);
	}

	/**
	 * Loads the given class, interface or trait.
	 *
	 * We'll return true if it was loaded.
	 *
	 * @param string $class
	 *
	 * @return bool
	 */
	public function loadClass(string $class) : bool {
		foreach(spl_autoload_functions() as $func)
			if(is_array($func) and $func[0] !== $this) {

			$this->classList->push($class);
			if(call_user_func($func, $class)) {
				break;
			}
		}

		$this->classList->next();

		return true;
	}

	/**
	 * Get an array of loaded file names in order of loading.
	 *
	 * @return string[]
	 */
	public function getFileNames() : array {
		$files = [];
		foreach($this->classList->getClasses() as $class) {
			// Push interfaces before classes if not already loaded
			try {
				$r = new \ReflectionClass($class);
				foreach($r->getInterfaces() as $inf)
					if(!in_array($name = $inf->getFileName(), $files)) {
						$files[] = $name;
				}

				if(!in_array($name = $r->getFileName(), $files)) {
					$files[] = $name;
				}
			} catch(\ReflectionException $e) {
				// We ignore all exceptions related to reflection because in
				// some cases class doesn't need to exist. We're ignoring all
				// problems with classes, interfaces and traits.
			}
		}

		return $files;
	}

}