<?php

/**
 * Compiler.php – php-compiler
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
use jacknoordhuis\phpcompiler\parser\NameResolver;
use jacknoordhuis\phpcompiler\parser\NamespaceVisitor;
use jacknoordhuis\phpcompiler\parser\NodeTraverser;
use jacknoordhuis\phpcompiler\resolver\FileList;
use jacknoordhuis\phpcompiler\resolver\NamespaceList;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;

/**
 * The base compiler class.
 */
abstract class Compiler {

	/** @var \PhpParser\PrettyPrinter\Standard */
	protected $printer;

	/** @var \PhpParser\Parser */
	protected $parser;

	/** @var \jacknoordhuis\phpcompiler\parser\NodeTraverser */
	protected $traverser;

	/** @var bool */
	protected $strict;

	/** @var \jacknoordhuis\phpcompiler\SourceList */
	protected $sourceList = [];

	/** @var \jacknoordhuis\phpcompiler\resolver\NamespaceList */
	protected $namespaceList;

	/** @var \PhpParser\NodeVisitor\NameResolver */
	protected $nameResolver;

	/**
	 * Compiler a new compiler instance.
	 *
	 * @param \PhpParser\PrettyPrinter\Standard               $printer
	 * @param \PhpParser\Parser                               $parser
	 * @param \jacknoordhuis\phpcompiler\parser\NodeTraverser $traverser
	 * @param bool                                            $strict
	 */
	public function __construct(Standard $printer, Parser $parser, NodeTraverser $traverser, bool $strict = true) {
		$this->printer = $printer;
		$this->parser = $parser;
		$this->traverser = $traverser;
		$this->strict = $strict;

		$this->sourceList = new SourceList();
		$this->namespaceList = new NamespaceList();

		$this->nameResolver = new NameResolver();
	}

	/**
	 * Prepare the output file and directory.
	 *
	 * @param string $output The absolute output file path.
	 *
	 * @return resource
	 *
	 * @throws \jacknoordhuis\phpcompiler\exception\CompilerException
	 */
	public function prepareOutput(string $output) {
		$dir = dirname($output);

		if(!is_dir($dir) && !mkdir($dir, 0777, true)) {
			throw new \RuntimeException("Unable to create output directory '{$dir}'.");
		}

		$handle = fopen($output, "w");

		if(!is_resource($handle)) {
			throw new CompilerException("Unable to open output file '{$output}' for writing.");
		}

		fwrite($handle, "<?php" . ($this->strict ? " declare(strict_types=1);" : "") . PHP_EOL);

		return $handle;
	}

	public function readFromFileList(FileList $list) : Compiler {
		foreach($list->getFileNames() as $source) {
			$this->read($source, false);
		}

		return $this;
	}

	public function read(string $file, bool $comments) {
		$parsed = $this->parser->parse($comments ? file_get_contents($file) : php_strip_whitespace($file)); //parse the php code into nodes
		$this->traverser->addVisitor($namespaceVisitor = new NamespaceVisitor());
		$this->traverser->traverseFile($parsed, $file);
		$this->traverser->removeVisitor($namespaceVisitor);

		foreach($namespaceVisitor->getNamespaceMap() as $namespace => $classes) {
			$this->namespaceList->addNamespace($namespace);
			foreach($classes as $name => $class) {
				$this->namespaceList->addClass($namespace, $name);
				$this->sourceList->addSource(new SourceClassStmts($class, $namespace, $name));
			}
		}
		foreach($namespaceVisitor->getGlobalNamespaceClasses() as $name => $class) {
			$this->sourceList->addSource(new SourceClassStmts($class, NamespaceList::GLOBAL_NAMESPACE, $name));
		}

		$this->sourceList->addSource(new SourceStmts($namespaceVisitor->getGlobalNamespace()));
	}

	public function write($handle) {
		$sources = $this->sourceList->getSources();

		$namespaces = $this->namespaceList->getNamespaces();

		foreach($namespaces as $namespace => $classes) {
			foreach($classes as $name) {
				$this->nameResolver->getNameContext()->addAlias(new Name\FullyQualified($namespace . "\\" . $name), $name, Use_::TYPE_NORMAL, [], true);
			}
		}

		array_reverse($namespaces, true);

		$this->traverser->addVisitor($this->nameResolver);

		foreach($namespaces as $namespace => $classes) {
			fwrite($handle, "namespace {$namespace} {" . PHP_EOL);
			foreach($classes as $key => $name) {
				foreach($sources as $k => $source)
					if($source instanceof SourceClassStmts) {
					if($source->getNamespace() === $namespace and $source->getName() === $name) {
						$pretty = $this->printer->prettyPrint($this->traverser->traverse([$source->getStmts()]));
						fwrite($handle, preg_replace(
							'/^(<\?php)?[\s]*(\/\*\*?.*?\*\/)?[\s]*(declare[\s]*\([\s]*strict_types[\s]*=[\s]*1[\s]*\);)?/s',
							'',
							$pretty
						) . PHP_EOL); // strip comments, whitespace <?php, and declare());
						unset($sources[$k]);
					}
				}
			}
			fwrite($handle, "}" . PHP_EOL);
		}

		fwrite($handle, "namespace {" . PHP_EOL);
		foreach($sources as $source) {
			if($source instanceof SourceClassStmts) {
				fwrite($handle, preg_replace(
						'/^(<\?php)?[\s]*(\/\*\*?.*?\*\/)?[\s]*(declare[\s]*\([\s]*strict_types[\s]*=[\s]*1[\s]*\);)?/s',
						'',
						$this->printer->prettyPrint($this->traverser->traverse([$source->getStmts()]))
					) . PHP_EOL); // strip comments, whitespace <?php, and declare());
			} else {
				fwrite($handle,$this->printer->prettyPrint($this->traverser->traverse($source->getStmts())) . PHP_EOL); // strip comments, whitespace <?php, and declare());
			}
		}
		fwrite($handle, "}" . PHP_EOL);

		$this->traverser->removeVisitor($this->nameResolver);
	}

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
	abstract public function getCode(string $file, bool $comments = true) : string;

}