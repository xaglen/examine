# Details #

_This is a proposed standard. Nothing is written in stone. Let me know what you think._

# XHTML #
  * see http://www.knowsystems.com/markup/reference/xhtml/requirements.html
  * must [validate](http://validator.w3.org/) as xhtml
  * all tags are lowercase
  * all tags are properly nested
  * all singleton tags need to properly closed
  * every file has a valid DOCTYPE. Let's go with XHTML 1.0 Transitional.

# PHP #
  * use the [PEAR coding standards](http://pear.php.net/manual/en/standards.php)
  * three exceptions: we don't need to follow their commenting specs for functions and we don't need to throw exceptions the same way that they do and we will use function opening braces on the same line
  * we will validate this using [CodeSniffer](http://pear.php.net/manual/en/package.php.php-codesniffer.php)

# MySQL #
  * use plurals for table names _I am open to rethinking this_
  * lowercase table and column names
  * use `_` to separate words in names (ie, first\_name instead of firstname)
  * primary keys are table\_name\_id (ie, people\_id) instead of just id
  * foreign keys are always the same name as the primary key being referenced
  * UPPERCASE reserved words in queries (`SELECT * FROM people`)

