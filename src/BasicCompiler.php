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

use function count;
use jacknoordhuis\phpcompiler\exception\CompilerException;
use const PHP_EOL;
use PhpParser\Lexer;
use PhpParser\Node;
use PhpParser\Node\Stmt\Namespace_;
use function preg_replace;
use function str_replace;
use const T_DOLLAR_OPEN_CURLY_BRACES;
use function var_dump;

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

		return $this->getCodeWrappedIntoNamespace($stmts);
	}

	/**
	 * Wrap the code into a namespace.
	 *
	 * @param array  $parsed
	 *
	 * @return string
	 */
	protected function getCodeWrappedIntoNamespace(array $parsed) : string {
		if(count($nodes = $this->getParsedCodeNamespaces($parsed)) !== 0) { //has a namespace node
			$pretty = "";
			foreach($nodes as $namespace) {
				if($pretty !== "") {
					$pretty .= PHP_EOL;
				}

				if($namespace->name === null) { //hack for
					$namespace->name = new Node\Name("global");
				}

				$pretty .= preg_replace('/^\s*(namespace.*);/im', '${1} {', $this->getPrettyPrinted([$namespace]), 1) . "\n}\n";
			}
		} else {
			$pretty = sprintf("namespace {\n%s\n}\n", $this->getPrettyPrinted($parsed));
		}

		return preg_replace('/(?<!.)[\r\n]+/', '', $pretty);
	}
	/**
	 * Check parsed code for having namespaces and return them.
	 *
	 * @param array $parsed
	 *
	 * @return array
	 */
	protected function getParsedCodeNamespaces(array $parsed) : array {
		// Namespaces can only be on first level in the code,
		// so we only make one check on it.
		return array_filter(
			$parsed,
			function($value) {
				return $value instanceof Namespace_;
			}
		);
	}

	/**
	 * Get pretty printed code with strict type declaration stripped.
	 *
	 * @param array $nodes
	 *
	 * @return string|string[]|null
	 */
	protected function getPrettyPrinted(array $nodes) {
		return preg_replace(
			'#^(<\?php)?[\s]*(\/\*\*?.*?\*\/)?[\s]*(declare[\s]*\([\s]*strict_types[\s]*=[\s]*1[\s]*\);)?#s',
			'',
			preg_replace('/^(namespace) global(;.*)\Z/msA', "$1$2", $this->printer->prettyPrint($nodes))
		);
	}

}