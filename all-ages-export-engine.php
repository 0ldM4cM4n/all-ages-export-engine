<?php

/**
 * webtrees module: all-ages-export-engine
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

use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Module\ModuleConfigInterface;
use Fisharebest\Webtrees\Module\ModuleConfigTrait;
use Fisharebest\Webtrees\Module\ModuleCustomInterface;
use Fisharebest\Webtrees\Module\ModuleCustomTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AllAgesExportEngine extends AbstractModule implements ModuleCustomInterface, ModuleConfigInterface
{
    use ModuleCustomTrait;
    use ModuleConfigTrait;

    // Module constants
    public const CUSTOM_MODULE       = 'all-ages-export-engine';
    public const CUSTOM_AUTHOR       = 'Bill Kochman';
    public const CUSTOM_WEBSITE      = 'https://github.com/0ldM4cM4n/all-ages-export-engine';
    public const CUSTOM_VERSION      = '1.0.0';
    public const CUSTOM_LAST_VERSION = 'https://raw.githubusercontent.com/0ldM4cM4n/all-ages-export-engine/main/module.php';
    public const CUSTOM_SUPPORT_URL  = 'https://github.com/0ldM4cM4n/all-ages-export-engine/issues';

    /**
     * {@inheritDoc}
     */
    public function title(): string
    {
        return I18N::translate('All Ages Export Engine');
    }

    /**
     * {@inheritDoc}
     */
    public function description(): string
    {
        return I18N::translate('Export a concise list of all individuals in a tree with birth and death dates.');
    }

    /**
     * {@inheritDoc}
     */
    public function customModuleAuthorName(): string
    {
        return self::CUSTOM_AUTHOR;
    }

    /**
     * {@inheritDoc}
     */
    public function customModuleVersion(): string
    {
        return self::CUSTOM_VERSION;
    }

    /**
     * {@inheritDoc}
     */
    public function customModuleLatestVersionUrl(): string
    {
        return self::CUSTOM_LAST_VERSION;
    }

    /**
     * {@inheritDoc}
     */
    public function customModuleSupportUrl(): string
    {
        return self::CUSTOM_SUPPORT_URL;
    }

    /**
     * Bootstrap the module
     */
    public function boot(): void
    {
        \Fisharebest\Webtrees\View::registerNamespace($this->name(), $this->resourcesFolder() . 'views/');
    }

    /**
     * Where does this module store its resources?
     */
    public function resourcesFolder(): string
    {
        return __DIR__ . '/resources/';
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function getAdminAction(ServerRequestInterface $request): ResponseInterface
    {
        $this->layout = 'layouts/administration';

        $tree_service = \Fisharebest\Webtrees\Registry::container()->get(\Fisharebest\Webtrees\Services\TreeService::class);
        $tree_list    = [];

        foreach ($tree_service->all() as $tree) {
            $tree_list[$tree->id()] = $tree->title();
        }

        return $this->viewResponse($this->name() . '::admin', [
            'title'       => $this->title(),
            'module_name' => $this->name(),
            'tree_list'   => $tree_list,
        ]);
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function postAdminAction(ServerRequestInterface $request): ResponseInterface
    {
        $params      = (array) $request->getParsedBody();
        $tree_id     = (int) ($params['tree_id'] ?? 0);
        $sort_order  = $params['sort_order'] ?? 'last_name';
        $export_type = $params['export_type'] ?? 'tsv';

        $tree_service = \Fisharebest\Webtrees\Registry::container()->get(\Fisharebest\Webtrees\Services\TreeService::class);
        $tree         = $tree_service->find($tree_id);

        if ($tree === null) {
            return redirect(route('module', [
                'module' => $this->name(),
                'action' => 'Admin',
            ]));
        }

        $rows = \Fisharebest\Webtrees\DB::table('individuals')
            ->where('i_file', '=', $tree->id())
            ->get();

        $individuals = [];

        foreach ($rows as $row) {
            $individual = \Fisharebest\Webtrees\Registry::individualFactory()->make($row->i_id, $tree);

            if ($individual === null || !$individual->canShow()) {
                continue;
            }

            $full_name  = $individual->getAllNames()[0] ?? [];
            $first_name = ($full_name['givn'] ?? '');
            $first_name = ($first_name === '' || $first_name === '?' || $first_name === '@P.N.') ? '[first name unknown]' : $first_name;
            $last_name  = ($full_name['surn'] ?? '');
            $last_name  = ($last_name === '' || $last_name === '@N.N.') ? '[last name unknown]' : $last_name;

            $birth_date = '';
            $birth      = $individual->getBirthDate();
            if ($birth->isOK()) {
                $birth_date = strip_tags($birth->display());
                if (strlen($birth_date) > 30) {
                    $birth_date = substr($birth_date, 0, 29) . '…';
                }
            }

            $death_date = '';
            $death      = $individual->getDeathDate();
            if ($death->isOK()) {
                $death_date = strip_tags($death->display());
                if (strlen($death_date) > 30) {
                    $death_date = substr($death_date, 0, 29) . '…';
                }
            }

            $individuals[] = [
                'first_name' => $first_name,
                'last_name'  => $last_name,
                'birth_date' => $birth_date,
                'death_date' => $death_date,
            ];
        }

        usort($individuals, function ($a, $b) use ($sort_order) {
            switch ($sort_order) {
                case 'first_name':
                    return strcasecmp($a['first_name'], $b['first_name']);
                case 'birth_date':
                    $yearA = $this->extractYear($a['birth_date']);
                    $yearB = $this->extractYear($b['birth_date']);
                    if ($yearA !== $yearB) {
                        return $yearA - $yearB;
                    }
                    return strcasecmp($a['last_name'], $b['last_name']);
                case 'last_name':
                default:
                    $last = strcasecmp($a['last_name'], $b['last_name']);
                    return $last !== 0 ? $last : strcasecmp($a['first_name'], $b['first_name']);
            }
        });

        switch ($export_type) {
            case 'csv':
                return $this->exportCsv($individuals, $sort_order);
            case 'print':
                return $this->exportPrint($individuals, $tree, $sort_order);
            case 'tsv':
            default:
                return $this->exportTsv($individuals, $sort_order);
        }
    }

    /**
     * Export as TSV for BBEdit
     */
    private function exportTsv(array $individuals, string $sort_order): ResponseInterface
    {
        // Calculate maximum width for each column
        $col1 = strlen('Last Name');
        $col2 = strlen('First Name');
        $col3 = strlen('Birth Date');
        $col4 = strlen('Death Date');

        foreach ($individuals as $individual) {
            $col1 = max($col1, strlen($individual['last_name']));
            $col2 = max($col2, strlen($individual['first_name']));
            $col3 = max($col3, strlen($individual['birth_date']));
            $col4 = max($col4, strlen($individual['death_date']));
        }

		if ($sort_order === 'first_name') {
		    $output = sprintf("%-{$col2}s\t%-{$col1}s\t%-{$col3}s\t%-{$col4}s\n\n",
		        'FIRST NAME', 'LAST NAME', 'BIRTH DATE', 'DEATH DATE');
		    foreach ($individuals as $individual) {
		        $output .= sprintf("%-{$col2}s\t%-{$col1}s\t%-{$col3}s\t%-{$col4}s\n",
		            $individual['first_name'],
		            $individual['last_name'],
		            $individual['birth_date'],
		            $individual['death_date']);
		    }
		} elseif ($sort_order === 'birth_date') {
		    $output = sprintf("%-{$col3}s\t%-{$col1}s\t%-{$col2}s\t%-{$col4}s\n\n",
		        'BIRTH DATE', 'LAST NAME', 'FIRST NAME', 'DEATH DATE');
		    foreach ($individuals as $individual) {
		        $output .= sprintf("%-{$col3}s\t%-{$col1}s\t%-{$col2}s\t%-{$col4}s\n",
		            $individual['birth_date'],
		            $individual['last_name'],
		            $individual['first_name'],
		            $individual['death_date']);
		    }
		} else {
		    $output = sprintf("%-{$col1}s\t%-{$col2}s\t%-{$col3}s\t%-{$col4}s\n\n",
		        'LAST NAME', 'FIRST NAME', 'BIRTH DATE', 'DEATH DATE');
		    foreach ($individuals as $individual) {
		        $output .= sprintf("%-{$col1}s\t%-{$col2}s\t%-{$col3}s\t%-{$col4}s\n",
		            $individual['last_name'],
		            $individual['first_name'],
		            $individual['birth_date'],
		            $individual['death_date']);
		    }
		}

        return response($output)
            ->withHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->withHeader('Content-Disposition', 'attachment; filename="all-ages-export.tsv"');
    }

    /**
     * Export as CSV for Excel/Numbers
     */
    private function exportCsv(array $individuals, string $sort_order): ResponseInterface
    {
		if ($sort_order === 'first_name') {
		    $output = "First Name,Last Name,Birth Date,Death Date\n";
		    foreach ($individuals as $individual) {
		        $output .= implode(",", [
		            '"' . str_replace('"', '""', $individual['first_name']) . '"',
		            '"' . str_replace('"', '""', $individual['last_name']) . '"',
		            '"' . str_replace('"', '""', $individual['birth_date']) . '"',
		            '"' . str_replace('"', '""', $individual['death_date']) . '"',
		        ]) . "\n";
		    }
		} elseif ($sort_order === 'birth_date') {
		    $output = "Birth Date,Last Name,First Name,Death Date\n";
		    foreach ($individuals as $individual) {
		        $output .= implode(",", [
		            '"' . str_replace('"', '""', $individual['birth_date']) . '"',
		            '"' . str_replace('"', '""', $individual['last_name']) . '"',
		            '"' . str_replace('"', '""', $individual['first_name']) . '"',
		            '"' . str_replace('"', '""', $individual['death_date']) . '"',
		        ]) . "\n";
		    }
		} else {
		    $output = "Last Name,First Name,Birth Date,Death Date\n";
		    foreach ($individuals as $individual) {
		        $output .= implode(",", [
		            '"' . str_replace('"', '""', $individual['last_name']) . '"',
		            '"' . str_replace('"', '""', $individual['first_name']) . '"',
		            '"' . str_replace('"', '""', $individual['birth_date']) . '"',
		            '"' . str_replace('"', '""', $individual['death_date']) . '"',
		        ]) . "\n";
		    }
		}

        return response($output)
            ->withHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->withHeader('Content-Disposition', 'attachment; filename="all-ages-export.csv"');
    }

    /**
     * Export as Print View
     */
    private function exportPrint(array $individuals, Tree $tree, string $sort_order): ResponseInterface
    {
        return $this->viewResponse($this->name() . '::print', [
            'title'       => I18N::translate('All Ages Export Engine'),
            'tree'        => $tree,
            'individuals' => $individuals,
            'sort_order'  => $sort_order,
        ]);
    }

    /**
     * Extract a 4-digit year from a date string for sorting purposes
     */
    private function extractYear(string $date): int
    {
        if (preg_match('/\b(\d{4})\b/', $date, $matches)) {
            return (int) $matches[1];
        }
        return 9999; // No year found — sort to end
    }

    /**
     * {@inheritDoc}
     */
    public function customModuleLatestVersion(): string
    {
        return '';
    }

} // End of AllAgesExportEngine class
