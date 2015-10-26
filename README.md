# MediaWiki_AuthIMAP
Extension for MediaWiki that allows authentication from IMAP


## Installation

### Requirements

This plugin requires the php-imap extension to be installed and enabled. 

#### Centos

```bash
yum install php-imap
service httpd restart
```

#### Ubuntu

```bash
apt-get install php-imap
php5enmod imap
a
```

### Install Extension Files
Export the repository to you WikiMedia Extensions directory. Usually simply extensions/ within MediaWiki's document root.

```
extensions/
├── AuthIMAP
│   └── Auth_imap.php
```

### LocalSettings.php
Include the following two lines at the bottom of your MediaWiki LocalSettings.php file:


```php
require_once('extensions/AuthIMAP/Auth_imap.php');
$wgAuth = new Auth_imap('<PHP IMAP $mailbox String>');
```


The **$mailbox** string is of the PHP IMAP imap_open mailbox string which is defined here: http://php.net/manual/en/function.imap-open.php

For example to authenticate against Google's Imap add the following:

```
require_once('extensions/Auth_IMAP/Auth_imap.php');
$wgAuth = new Auth_imap('{imap.gmail.com:993/ssl}INBOX');
```


