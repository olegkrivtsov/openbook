# OpenBook - The Open-Source Tool for Generating HTML Books about the PHP Programming Language

The aim of this project is to develop a convenient tool for generating online books on PHP programming language. I want that the books generated be readable in any browser and on any-sized device (from smartphones to desktops). The tool gets the book sources in a Markdown format (particularly the dialect of Markdown proposed by [Leanpub](https://leanpub.com/help/manual) website) and produces HTML files readable in a browser on output. Then the generated files can be published on the Internet on a cheap or free web hosting.  

## Background 

This project originated as an HTML files generator for my open-source PHP programming book [Using Zend Framework 3](https://github.com/olegkrivtsov/using-zend-framework-3-book). I wanted to have a console tool for generating the static HTML files of the book from Markdown sources and referenced PNG images. I didn't find ready tools that are convenient enough, so I decided to create my own tool.

## Features

Currently the tool can do the following:
 
 * Generate HTML files from Markdown (`.md`) sources.
 * 

# License

This book uses the [MIT](https://en.wikipedia.org/wiki/MIT_License) license. It is a very permissive license, so you can use this project without almost any limitations.

## Installation

To install the tool, you first need to install the PHP engine (version 5.6 or later will work). 

In Linux Ubuntu you can do that with the following command:

`sudo apt-get install php`

In Linux CentOS you can do that with the command:

`yum install php`

If the instructions above do not work in your OS, please [search](https://www.google.com/search?q=install+php) for relevant instructions.

Then [clone](https://help.github.com/articles/cloning-a-repository/) this repository or [download](https://github.com/olegkrivtsov/openbook/archive/master.zip) it as a ZIP archive and unpack somewhere.

Go to the directory where you put the files and type the following commands to install the PHP dependency packages:

```
php composer.phar self-update
php composer.phar install
```

Once that's done, you can run the tool with the following command:

`php openbook.php [options] <book_dir>`

## Command-Line Usage



## Using the Tool


### Book Structure

If you want to briefly review an example of a ready book, take a look at the GitHub repository where I store the sources of my [Using Zend Framework 3](https://github.com/olegkrivtsov/using-zend-framework-3-book) book. If you find the structure of that repository confusing, please see below for clarification.

The files of the book are organized in the following manner:

```
<book_dir>/
  manuscript/
    en/
      images/
      Book.txt
      chapter1.txt
      chapter2.txt
      ...
    ru/
      images/
    another-lang/
      images/
  openbook.json  
```

The *manuscript* directory is the directory where Markdown files of the book are stored. The *en/*, *ru/*, *another-lang/* are directories where the Markdown files for a specific language are stored. The *images* directory is the directory where you should place the PNG images you want to reference in the book text. 

#### Options File

`openbook.json` should be a file in JSON format containing book properties, like its title,
subtitle, copyright information, etc. An example of a real-life `openbook.json` is presented below:

```
{
    "book_title": "Using Zend Framework 3",
    "book_subtitle": "A free and open-source book about Zend Framework",
    "copyright": "2018 by Oleg Krivtsov",
    "license": "https://creativecommons.org/licenses/by-nc-sa/4.0/",
    "book_website": "https://olegkrivtsov.github.io/using-zend-framework-3-book/html",
    "keywords": [
        "php",
        "zend framework",
        "book",
        "tutorial",
        "documentation",
        "learn",
        "free"
    ],
    "links": {
        "Home": "https://olegkrivtsov.github.io/using-zend-framework-3-book/html",
        "Samples": "https://github.com/olegkrivtsov/using-zf3-book-samples",
        "Class Reference": "https://olegkrivtsov.github.io/zf3-api-reference/html/",
        "Contribute": "https://github.com/olegkrivtsov/using-zend-framework-3-book"
    },
    "languages": {
        "en": "English",
        "ru": "Русский",
        "es": "Español"
    },
    "incomplete_translations": ["es"],
    "google_analytics": {
        "enabled": true,
        "account_id": "UA-80824388-1"
    },
    "google_adsence": {
        "enabled": true, 
        "contents_ad": "data/contents_ad.js",
        "chapter_upper_ad": "data/upper_ad.js",
        "chapter_bottom_ad": "data/bottom_ad.js"
    },
    "disqus": {
        "enabled": true,
        "src": "//using-zend-framework-3-book.disqus.com/embed.js"
    }
}
```

The `Book.txt` file should contain the list of chapters to include into the book:

```
preface.txt
acknowledgments.txt
chapter1.txt
chapter2.txt
chapter3.txt
...
```

You can find an example of a real-life book here: [https://github.com/olegkrivtsov/using-zend-framework-3-book](https://github.com/olegkrivtsov/using-zend-framework-3-book). The OpenBook tool was mainly developed to generate the HTML files of that book.

## Generating Your Book



Finally, generate the book with the following command:

```
php openbook.php /path/to/your/book
```

If everything is OK, you'll find the HTML files in `/path/to/your/book/html` directory.

That's all, enjoy and do not hesitate to [report](https://github.com/olegkrivtsov/openbook/issues) bugs and contribute (see below)!



# Contributing

You may contribute in the following ways:

 * If you want to report a bug, please use the [Issues](https://github.com/olegkrivtsov/openbook/issues) page.
 * If you want to propose an improvement, please make a fork of this repository, edit the source code and finally make a [pull](https://help.github.com/articles/about-pull-requests/) request.
 
# Your Feedback

If you want to contact me personally to share your imotions about this project, please use the olegkrivtsov at gmail dot com email address. But please note that this is my personal address and I will ignore non-relevant messages.
