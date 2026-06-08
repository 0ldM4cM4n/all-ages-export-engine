![B&C Mods](https://raw.githubusercontent.com/0ldM4cM4n/all-ages-export-engine/main/assets/images/B-C-Mods-Logo-02-optimized.png)

# all-ages-export-engine

This [webtrees](https://www.webtrees.net) module is a clean, simple admin utility that exports a concise list of all individuals in a family tree, displaying four fields only: Last Name, First Name, Birth Date, and Death Date.

## Features
- ✅ Three sort options: Last Name, First Name, or Birth Date
- ✅ Three export formats: TSV, CSV, and Print View
- ✅ Fixed-width column alignment in text exports
- ✅ Long field entries automatically truncated to preserve column alignment
- ✅ Unknown first and last names clearly labeled
- ✅ No core Webtrees files modified — update safe!

## Compatibility

### webtrees 2.2
This module is developed and tested with webtrees 2.2.6 running under PHP 8.4.

### webtrees 2.1
This module should work with webtrees 2.1.x but has not been explicitly tested.

### webtrees 2.0 and lower
This module **will not work** with webtrees versions lower than 2.1.

## Installation Instructions
1. Download the zip archive of this repository from GitHub.
2. Unzip the archive.
3. Place the entire `all-ages-export-engine` folder into the `modules_v4` directory on your server.
4. That's it! Webtrees will automatically detect and load the module.

## Usage
1. Go to the **Control Panel**.
2. Find **All Ages Export Engine** in the modules list and click the **wrench** icon next to it.
3. Select a family tree from the dropdown.
4. Choose a sort order (see **Notes On Sort Orders** below).
5. Choose an export format (see **Export Formats** below).
6. Click **Generate Export**.

## Export Formats

### TSV (Tab Separated Values)
Produces a plain text file with fixed-width padded columns for clean alignment in any text editor such as BBEdit. The sort column always appears first. The file can also be opened directly in Apple Numbers or Microsoft Excel, where it will display in neat, properly aligned columns.

### CSV (Excel / Numbers)
Produces a standard comma-separated values file optimized for use in spreadsheet applications such as Apple Numbers or Microsoft Excel. The sort column always appears first.

### Print View
Displays the exported list as a formatted, printer-friendly HTML page within your Webtrees browser window. It does not send anything directly to a printer. If you wish to produce a physical printed copy, use your web browser's built-in print function (File → Print, or Cmd+P / Ctrl+P) once the Print View is displayed on screen. A printer must be connected to your device to print a hard copy.

## Notes On Sort Orders

### Last Name
Sorts alphabetically by last name, with first name as a tiebreaker. This is the default sort order and is recommended for most use cases, as it groups family members together and avoids having to sift through large numbers of individuals with the same first name.

### First Name
Sorts alphabetically by first name. The first name column will appear first in the export output.

### Birth Date
Due to the many different ways in which users enter birth date information — including exact dates, approximate dates (e.g. "about 1534"), date ranges (e.g. "between 1580 and 1581"), and partial dates (e.g. "February 1612") — it is not practical to sort by the full date string. Instead, the Birth Date sort extracts the first four-digit year found in the birth date field and sorts numerically by that year. Individuals with no birth date recorded will appear at the end of the list. The birth date column will appear first in the export output.

## Notes On Output Formatting
Due to the wide variety of ways in which different users enter data into Webtrees records, the exported output may not always be perfectly aligned in text files. Factors such as inconsistent naming conventions, unusually long names, extended date ranges, and incomplete records are beyond the control of this module. We have done our best to produce clean, readable output, but some manual adjustment may occasionally be necessary.

To help preserve column alignment, any field entry exceeding 30 characters in length will be automatically truncated and followed by an ellipsis (…). This applies primarily to birth and death date fields where users have entered extended date ranges.

## Notes On Unknown Names
Where a user has left the first name field empty in a record, it will be replaced in the export with **[first name unknown]**. Where a user has left the last name field empty, it will be replaced with **[last name unknown]**. In cases where a user has left both fields empty in a record, both replacements will apply, resulting in **[first name unknown]** and **[last name unknown]** appearing in their respective columns.

## Known Limitations
- Due to the wide variety of data entry styles used by different users, perfect column alignment in text exports cannot always be guaranteed. See **Notes On Output Formatting** above.
- Birth Date sorting is based on year only. See **Notes On Sort Orders** above.

## Upgrade Safety
This module operates entirely within the Webtrees module system. No core Webtrees files are modified.

## Privacy, Telemetry, Tracking
Privacy: yes. Tracking: no.

The module will check for the latest available version whenever the Webtrees Control Panel is opened. It checks a URL on github.com only.

## Credits
Developed by Bill Kochman with assistance from Claude (Anthropic).

## License
Copyright (C) 2026 Bill Kochman.

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

## Warranty
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
See the GNU General Public License for more details:
https://www.gnu.org/licenses/
