# WordPress to Markdown

A WP-CLI command to convert WordPress content to Markdown files.

## Requirements

- WordPress
- PHP 7.0
- WP-CLI

## Getting Started

You'll need WP-CLI [installed](https://wp-cli.org/#installing) and ready to use. Go to your WordPress's root directory (where `wp-config.php` is located) and run the following command:

```bash
❯ wp --version
WP-CLI 2.4.0
```
The best way to install this command is in your `/wp-content/mu-plugins` directory. First, clone this repo:

```bash
# From the WordPress root ...
❯ git clone git@github.com:joshcanhelp/wordpress-to-markdown.git wp-content/mu-plugins/wp-to-md
# ... or https://github.com/joshcanhelp/wordpress-to-markdown.git

Cloning into 'wp-content/mu-plugins/wp-to-md'...
# ... etc ...

❯ echo "if ( class_exists( 'WP_CLI' ) ) require_once 'wp-to-md/wp-to-md.php';" > wp-content/mu-plugins/wp-to-md.php
❯ wp wptomd-types
```