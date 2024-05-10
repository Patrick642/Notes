Notes
========================

 An application for taking simple notes written in PHP using the Symfony framework. 

Requirements
------------

  * PHP 8.2.0 or higher
  * PHP extensions: PDO, SQLite, Ctype, iconv, PCRE, Session, SimpleXML, and Tokenizer
  * [Database supported by Doctrine][1]
  * Mail server
  * Git
  * Composer

Installation
------------

1. Clone the repository and install the packages required for the application to run:
```bash
git clone https://github.com/Patrick642/Notes.git
cd Notes
composer install
```

2. Configure the database connection and server version in the `.env` file, there is example for MySQL database:

```bash
DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=mariadb-10.5.8"
```
More about configuring the database [here][2]

3. Configure Mailer DSN in the `.env` file:

```bash
MAILER_DSN=smtp://user:pass@smtp.example.com:port
```
More about configuring Mailer [here][3]

4. If you change the environment from `dev` to `prod` (you can do it in the `.env` file), you also need to compile the assets:

```bash
symfony console asset-map:compile
```

[1]: https://www.doctrine-project.org/projects/doctrine-dbal/en/4.0/reference/introduction.html
[2]: https://symfony.com/doc/current/doctrine.html#configuring-the-database
[3]: https://symfony.com/doc/current/mailer.html#transport-setup