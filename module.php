<?php

/**
 * webtrees module: AllAgesExportEngine
 * A clean, simple admin utility for exporting a concise list of all
 * individuals in a Webtrees family tree. Displays four elements only:
 * First Name, Last Name, Birth Date, and Death Date (if applicable).
 *
 * Copyright (C) 2026 Bill Kochman.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details:
 * <https://www.gnu.org/licenses/>
 */

declare(strict_types=1);

namespace BillKochman\WtModule\AllAgesExportEngine;

use Composer\Autoload\ClassLoader;

$loader = new ClassLoader();
$loader->addPsr4('BillKochman\\WtModule\\AllAgesExportEngine\\', __DIR__);
$loader->register();

require_once __DIR__ . '/all-ages-export-engine.php';

return new AllAgesExportEngine();
