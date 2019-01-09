<?php

/**
 * ILibraryHolder.php – php-compiler
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

namespace jacknoordhuis\phpcompiler\builder\library;

interface ILibraryHolder {

	public function getLibraries() : array;

	public function addLibrary(Library $library) : ILibraryHolder;

	public function addLibraries(array $libraries) : ILibraryHolder;

	public function buildLibraries(array &$built, int &$count, int &$skipped) : void;

}