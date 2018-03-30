# OpenBook

This project is an open-source book generator. It gets the book in Markdown format (particularly its 
[Leanpub](https://leanpub.com/help/manual) flavor) and produces HTML files on output. 

## Book Structure

Organise your book files in the following manner:

```
<book_dir>/
  manuscript/
    en/
      chapter1.txt
      chapter2.txt
      Book.txt
  openbook.json  
```

Here, the `openbook.json` should be a file in JSON format containing book properties, like its title,
subtitle, copyright information, etc. An example of a real-life `openbook.json` is presented below:

```
{
    "book_title": "Using Zend Framework 3",
    "book_subtitle": "A free and open-source book about Zend Framework",
    "copyright": "(c) 2018 by Oleg Krivtsov",
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

You can find an example of a real-life book here: [https://github.com/olegkrivtsov/using-zend-framework-3-book](https://github.com/olegkrivtsov/using-zend-framework-3-book).

## Generating the Book

To generate HTML file for the book, you first need to install PHP like the following (version 5.5 or later):

`sudo apt-get install php`

Then clone OpenBook from this page or download it as a ZIP archive and unpack somewhere.

Then open the OpenBook directory and type the following to install the dependencies:

```
php composer.phar self-update
php composer.phar install
```

Finally, generate the book with the following command:

```
php openbook.php /path/to/your/book
```

If everything is OK, you'll find the HTML files in `/path/to/your/book/html` directory.

That's all, enjoy!
