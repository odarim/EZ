# MyDEED Project

![Symfony](https://img.shields.io/badge/Symfony-6.3-purple.svg)
![PHP](https://img.shields.io/badge/PHP-8.2-blue.svg)

## 📖 Project description

This project is made with [Symfony][1] 6.3.

## 🚀 Environment Setup

### 🐳 Needed tools

1. PHP 8.2 or higher;
2. NodeJS v16.* or higher.
3. Composer
4. PostgreSQL PHP extension enabled;
5. and the [usual Symfony application requirements][2].
6. Clone this project: `git clone https://github.com/odarim/EZ.git srcs`
7. Move to the project folder: `cd srcs`


### 🛠️ Environment configuration

1. Create a local environment file (`cp .env .env.local`) if you want to modify any parameter
2. If you want to modify database configuration, edit this line in `.env` file with your own configuration
      `DATABASE_URL="postgresql://app:!ChangeMe!@127.0.0.1:5432/app?serverVersion=15&charset=utf8"`


### 🔥 Application execution

1. Install the backend dependencies: `composer install`.
2. Create a database & tables with `php bin/console d:d:c` then `php bin/console make:migration`
   and `php bin/console migration:migrate` or force with `php bin/console d:s:u -f`
3. Create default user with command `php bin/console app:create-user`, adding the argument `--admin` or `-a` if you want to create and administrator.
4. Install the frontend dependencies: `npm install` or `yarn install`.
5. for getting the tom-select.default.css: `php bin/console importmap:require tom-select/dist/css/tom-select.default.css`
6. For the development purpose, run `yarn watch` or `npm run watch`. For the production version, run `yarn build` or `npm run build`.
7. Start the server with Symfony: `symfony serve`.
   Then access the application in your browser at the given URL ([https://localhost:8000](https://localhost:8000) by default).
   If you don't have the Symfony binary installed, run `php -S localhost:8000 -t public/`
   to use the built-in PHP web server or [configure a web server][3] like
   Apache or Nginx to run the application.

[1]: https://symfony.com/doc/6.3/index.html
[2]: https://symfony.com/doc/6.3/setup.html#technical-requirements
[3]: https://symfony.com/doc/6.3/setup/web_server_configuration.html
