# Notes
An application for taking simple notes written in PHP using the Symfony framework.

## Demo
🔗[Demo version of the application](https://notes-demo.free.nf)

## Requirements
  * PHP 8.2.0 or higher
  * PHP extensions: PDO, SQLite, Ctype, iconv, PCRE, Session, SimpleXML, and Tokenizer
  * [Database engine supported by Doctrine][1]
  * Mail server
  * Composer
  * Git (optional, if you decide to clone the repository instead of manual downloading)

## Installation
**1.** Get the application code

**Option 1.** Download it and unzip the ZIP file.

**Option 2.** Clone the repository:

```
git clone https://github.com/Patrick642/Notes.git
```

**2.** Go to root directory of the application:

```
cd Notes
```

**3.** Download the packages required for the application to run:

```
composer install
```

**4.** Configure the database connection and server version in the `.env` file, there is example for MySQL database:

```
DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=mariadb-10.5.8"
```

More about configuring the database [here][2]

**5.** If you don't have a database yet, create it:

```
php bin/console doctrine:database:create
```

**6.** Update database schema:

```
php bin/console doctrine:migrations:migrate
```

**7.** Configure Mailer DSN in the `.env` file:

```
MAILER_DSN=smtp://user:pass@smtp.example.com:port
```

More about configuring Mailer [here][3]

**8.** Set up sender details `config/packages/mailer.yaml`

**9.** If you change the environment from `dev` to `prod` (you can do it in the `.env` file), you also need to compile the assets:

```
php bin/console asset-map:compile
```

[1]: https://www.doctrine-project.org/projects/doctrine-dbal/en/4.0/reference/introduction.html
[2]: https://symfony.com/doc/current/doctrine.html#configuring-the-database
[3]: https://symfony.com/doc/current/mailer.html#transport-setup
