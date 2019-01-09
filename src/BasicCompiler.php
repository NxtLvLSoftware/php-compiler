<?php

/**
 * BasicCompiler.php – php-compiler
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

namespace jacknoordhuis\phpcompiler;

use jacknoordhuis\phpcompiler\exception\CompilerException;
use PhpParser\Node\Stmt\Namespace_;

/**
 * The basic compiler which performs next to no changes to to the code.
 */
class BasicCompiler extends Compiler {

	/**
	 * Get a pretty printed string of code from a file while applying visitors.
	 *
	 * @param string $file
	 * @param bool $comments
	 *
	 * @return string
	 *
	 * @throws \jacknoordhuis\phpcompiler\exception\CompilerException
	 */
	public function getCode(string $file, bool $comments = true) : string {
		if(empty($file) or !is_file($file)) {
			throw new CompilerException("Invalid input filename '{$file}' provided.");
		}

		$content = $comments ? file_get_contents($file) : php_strip_whitespace($file);

		$parsed = $this->parser->parse($content);
		$stmts = $this->traverser->traverseFile($parsed, $file);
		$pretty = $this->printer->prettyPrint($stmts);

		$pretty = preg_replace(
			'#^(<\?php)?[\s]*(/\*\*?.*?\*/)?[\s]*(declare[\s]*\([\s]*strict_types[\s]*=[\s]*1[\s]*\);)?#s',
			'',
			$pretty
		);

		return $this->getCodeWrappedIntoNamespace($parsed, $pretty);
	}

	/**
	 * Wrap the code into a namespace.
	 *
	 * @param array  $parsed
	 * @param string $pretty
	 *
	 * @return string
	 */
	protected function getCodeWrappedIntoNamespace(array $parsed, string $pretty) : string {
		if($this->parsedCodeHasNamespaces($parsed)) {
			$pretty = preg_replace('/^\s*(namespace.*);/im', '${1} {', $pretty, 1) . "\n}\n";
		} else {
			$pretty = sprintf("namespace {\n%s\n}\n", $pretty);
		}

		return preg_replace('/(?<!.)[\r\n]+/', '', $pretty);
	}
	/**
	 * Check parsed code for having namespaces.
	 *
	 * @param array $parsed
	 *
	 * @return bool
	 */
	protected function parsedCodeHasNamespaces(array $parsed) : bool {
		// Namespaces can only be on first level in the code,
		// so we only make one check on it.
		$node = array_filter(
			$parsed,
			function($value) {
				return $value instanceof Namespace_;
			}
		);

		return !empty($node);
	}

}