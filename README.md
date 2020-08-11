# Library-Bookshelves
Backend interface for importing and maintaining a Library of Congress call number - bookshelf lookup system.
Supports LCCN lookup and shelf+floor lookup.

## Requirements
* PHP web server
* SQL database if using built in DB Cacher

## Setup
To build a framework for the provided DB Cacher do the following.
If you're building your own caching and logging backend you can ignore this setup.
* Create dbcx.php from dbcx.example.php
* Create table named `shelves` with the following columns:
  * bid; int; primary
  * floor; int; primary
  * shelf; varchar; primary
  * start_norm; varchar
  * start_denorm; varchar
  * end_norm; varchar
  * end_denorm; varchar
  * update; timestamp; on update current_timestamp
* Create table named `buildings` with the following columns
  * bid; int primary auto-increment
  * building_name; varchar
  * update; timestamp; on update current_timestamp
* Create table named `logs` with the following columns
  * lid; int; primary auto-increment
  * message; longtext
  * floor; int; nullable
  * shelf; varchar; nullable
  * created; timestamp; default current_timestamp

## Usage
* Populate building table with some buildings. By default, `dummy` is used.
* Visit import.php?building=[building_name] to import to the DB, where `building_name` exists in the building table.
* Visit lookup.php?call=[LCCN] to get redirected to a frontend page of your design. If your collection is 100% sequential the debug view should return only one shelf, otherwise the redirect will choose the first shelf returned.
* Visit shelves.php?floor=[floor]&shelf_start=[shelf]&shelf_end=[shelf] to get a JSON endpoint with an LCCN range that spans those shelves. This can be useful for range highlighting and interactivity on your frontend.

To fully utilize this project you need to build a frontend display.
A frontend could be anything from a simple webpage that displays a static image with overlays to point out what shelf/range/stack the book can be found on.
All the way up to an interactive GIS/Map integration that highlights individual shelves and provides pop-ups for call number ranges.
By default lookup.php can redirect to a frontend page and pass along the denormalized LCCN, floor #, shelf #, and building name.
Of course this can be extended to meet your needs.

## Design
This application is designed to be forked and extended to meet your library's need for importing however you currently manage stacks and ranges.
The workflow is as follows:
1. Visit import.php to kick off an import. I suggest building a script around this page to automate creating a local copy of whatever stack management you use.
1. import.php creates a Caching object implementing Shelf_Call_Number_Cache_Interface.
    * Build your own Cacher to store shelf data into whatever caching backend you decide is best.
    * Null_DB_Cache is provided as an example of how to build a database caching backend.
1. Your Caching object should create a Reader object implementing Shelf_Call_Number_Reader_Interface.
    * Build your own Reader to read from your current stack management solution.
    * Null_Reader is provided as an example of how to build a Reader.
1. Factories sit between Cacher and Reader creation to help swapping different caching and reading implementations.
1. Null_Reader generates some fake shelf data for up to 499 shelves using sequential call numbers of the form `AB123.4.C5.D[#] 2010`.
1. Null_DB_Cache takes shelf data and stashes it into a database.

### Cachers
Cachers are designed as a modular solution to storing shelf data into whatever caching backend you prefer.
By default Library-Maps ships with a bare-bones DB backend.
Feel free to flesh it out to meet your needs, or run with the stock design.
Alternatively, you can build your own in memory solution with something like Memcached or Redis, or even a flat-file solution using your favorite structured data files, like JSON.

To build a Cacher, implement the Shelf_Call_Number_Cache_Interface and follow the comments to see how you will be receiving data and how you should present data.

### Readers
Readers are designed as a modular solution to reading in shelf/stack/range data based on your current shelf management solution. For example, we built this to read in an excel spreadsheet where each row contained:
* Shelf number
* Hyphen separated call number range
* Sheet title as floor number

To build a reader, implement the Shelf_Call_Number_Reader_Interface and follow the comments to see how you should be returning data.

### Others
Parsers and Loggers exist to make it easier to see how each job is handled. Optimistically these two would follow the Readers/Cachers pattern of modularity.
For now, out of the box, they're a little trickier to swap out, but certainly not impossible.

## Parse Strategy
The built in LCCN parser is based off [libraryhackers' library-callnumber-lc](https://github.com/libraryhackers/library-callnumber-lc) python parser.
Attribution and license information can be found in [Regex_Call_Number_Parser.php](../main/Classes/Parsers/Regex_Call_Number_Parser.php).

A pseudo-magical regex breaks the LCCN into it's parts based on Library Congress' definition.
The parts are then used to normalize the LCCN. Normalized LCCNs are trivially sortable and comparable using lexicographical ordering.

* Break LCCN into Alpha, Num, Dec, Cutter 1-3 (alpha and num parts), and Extra.
    * Alpha is padded to length 3 with spaces to the right
    * Num is padded to length 4 with 0s to the left
    * Dec is padded to length 2 with 0s to the right
    * C 1-3 Alpha is padded to length 1 with spaces to the right
    * C 1-3 Num is padded to length 3 with 0s to the right
    * Extra is trimmed of whitespace on either end
* `AB123.4.C5.D67 2010` takes the form `AB 012340C500D670 000 2010`
    * With groups highlighted: `(AB)[123].[4].(C)[5].(D)[67] {2010}` takes the form `(AB )[0123][40](C)[500](D)[670]( )[000] {2010}`
    
Parsing, normalization, and caching work best when the reference call numbers or less specific than the lookup values. Eg: `AB123.4` is better to store than `AB123.4.C5.D67 2010` but vice-versa for looking up.
