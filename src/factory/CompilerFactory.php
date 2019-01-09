<?php

/**
 * CompilerFactory.php – php-compiler
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

namespace jacknoordhuis\phpcompiler\factory;

use jacknoordhuis\phpcompiler\BasicCompiler;
use jacknoordhuis\phpcompiler\Compiler;
use jacknoordhuis\phpcompiler\parser\DirVisitor;
use jacknoordhuis\phpcompiler\parser\FileVisitor;
use jacknoordhuis\phpcompiler\parser\NodeTraverser;
use jacknoordhuis\phpcompiler\parser\StrictTypesVisitor;
use PhpParser\Lexer;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;

/**
 * The compiler factory class is the simplest way to create a compiler instance.
 */
class CompilerFactory {

	/**
	 * Create a new compiler instance.
	 *
	 * @param bool $dir    If true __DIR__ contants will be replaced with the real directory.
	 * @param bool $file   If true __FILE__ contants will be replaced with the real file name.
	 * @param bool $skip   If true files using __DIR__ and __FILE__ constants will not be compiled.
	 * @param bool $strict If the output file will have strict types enabled.
	 *
	 * @return \jacknoordhuis\phpcompiler\Compiler
	 */
	public static function create(bool $dir = false, bool $file = false, bool $skip = false, bool $strict = true) : Compiler {
		return new BasicCompiler(new Standard(), self::createParser(), self::createTraverser($dir, $file, $skip), $strict);
	}

	/**
	 * Get the parser to use.
	 *
	 * @return \PhpParser\Parser
	 */
	protected static function createParser() : Parser {
		if(class_exists(ParserFactory::class)) {
			return (new ParserFactory())->create(ParserFactory::PREFER_PHP7, self::createLexer());
		}

		return new Parser\Multiple([
			new Parser\Php7(self::createLexer()),
			new Parser\Php5(self::createLexer()),
		]);
	}

	/**
	 * Create a new default lexer instance.
	 *
	 * @return \PhpParser\Lexer
	 */
	protected static function createLexer() : Lexer {
		return new Lexer([
			"usedAttributes" => [
				"comments",
				"startLine",
				"endLine",
				"startFilePos",
				"endFilePos",
			]
		]);
	}

	protected static function createTraverser(bool $dir, bool $file, bool $skip) : NodeTraverser {
		$node = new NodeTraverser();

		if($dir) {
			$node->addVisitor(new DirVisitor($skip));
		}

		if($file) {
			$node->addVisitor(new FileVisitor($skip));
		}

		$node->addVisitor(new StrictTypesVisitor());

		return $node;
	}
}