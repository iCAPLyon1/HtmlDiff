HtmlDiff
========

 A PHP5 library that diffs (compares) HTML files.

 This library is actually a transfer of the daisy-diff library for PHP, found [here](http://code.google.com/p/daisydiff/source/browse/trunk/daisydiff-php/) for gitgub, meeting the PSR-0 standards.
 The library was developped in 2008 and is used today by many wiki platforms (wikipedia, wikihub, gamepedia, etc). It is part of code of the MediaWiki package.


 Setup
-------

## Requirements
- PHP >=5.3.0
- Pimple 1.0.*
- Silex 1.0.*

## Installation

The easiest way to install HtmlDiff is using [Composer](https://github.com/composer/composer)
with the following requirement:

```json
    {
        "require": {
        	...
            "icap/html-diff": ">=1.0.1"
        }
    }
```

Alternatively, you can [download the archive](https://github.com/iCAPLyon1/HtmlDiff/archive/master.zip) 
and add the src/ folder to PHP's include path:
```php
    set_include_path('/path/to/src' . PATH_SEPARATOR . get_include_path());
```
## Usage

To find and render the differences between two html blocks html you need include the *HtmlDiff* class

```php
	use Icap\HtmlDiff\HtmlDiff;
```
and then initialize a new HtmlDiff class object with the following attributes:
- the old html text/block (String)
- the new html text/block (String)
- the option to enable or not formatting changes (Boolean) (set this to true if beside the adds and removes you also want to display the different style modifications of the nodes/elements)

Then call the outputDiff function of the class to retrieve the result with the compared version and the modifications.

```php
	$htmlDiff = new HtmlDiff($oldText, $newText, true);
	$out = $htmlDiff->outputDiff();
	//Then
	$out->toString();//to get the compared version
	//And
	$out->getModifications();//to retrieve the number of differences/modifications between the two blocks.
```
The output is a [ChangeText](https://github.com/iCAPLyon1/HtmlDiff/blob/master/src/Icap/HtmlDiff/Html/ChangeText.php)

You can always refer to the demo/examples for further help.
In the given examples we use two different styles to render the compared html result. Feel free to copy and use these styles in your application.

Modifications compared to the original library
------------------------------------------------

- In the ChangeText object a new attribute was added (modifications) to count the differences found between the two texts. These differences are given in a form of an array ('added' =>  #, 'changed' => #, 'removed' => #)
- The details tooltip was removed
- All the MediaWiki general functions (which are defined [here](http://svn.wikimedia.org/viewvc/mediawiki/trunk/phase3/includes/GlobalFunctions.php?view=markup&pathrev=58267)) used by the HtmlDiff module, were replaced with some simple/dummy ones so as the module runs without them.
- In the converted html output all the 'added' and 'deleted' span tags were replaced by 'ins' and 'del' tags respectively for WAI compliance reasons.

Tests
-------

Since the library is a tranfer of an already tested (hopefully) library, no tests were created or executed.

Known issues
-------------

There is a known issue with table comparison reported [here](http://code.google.com/p/daisydiff/issues/detail?id=8)
Thow it is referring to the java version of the library, the same issue exists in the PHP version.







