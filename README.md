# README #

This README would normally document whatever steps are necessary to get your application up and running.

### What is this repository for? ###

* Unisharp News Utils

### How do I get start? ###

* install [composer](https://getcomposer.org/)
* `composer install`
* `php index.php`



### Example

```
for d in `seq -w 30` ; do php index.php --action=fetch-count-for-link --date=2014-11-$d --app-id=1420881371482354 --app-secret=SECRET; done
 ```
