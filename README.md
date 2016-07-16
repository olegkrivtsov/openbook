# OpenBook

This project is an open-source book generator. It gets the book in Markdown format (particularly its 
[Leanpub](https://leanpub.com/help/manual) flavor) and produces HTML files on output. 

# Book Structure

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
subtitle, copyright information, etc.

You can find an example of a real-life book here: [https://github.com/olegkrivtsov/using-zend-framework-3-book](https://github.com/olegkrivtsov/using-zend-framework-3-book).

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