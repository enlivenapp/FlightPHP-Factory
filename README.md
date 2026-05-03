[![Stable? Not Quite Yet](https://img.shields.io/badge/stable%3F-not%20quite%20yet-blue?style=for-the-badge)](https://packagist.org/packages/enlivenapp/flight-factory)
[![License](https://img.shields.io/packagist/l/enlivenapp/flight-factory?style=for-the-badge)](https://packagist.org/packages/enlivenapp/flight-factory)
[![PHP Version](https://img.shields.io/packagist/php-v/enlivenapp/flight-factory?style=for-the-badge)](https://packagist.org/packages/enlivenapp/flight-factory)
[![Monthly Downloads](https://img.shields.io/packagist/dm/enlivenapp/flight-factory?style=for-the-badge)](https://packagist.org/packages/enlivenapp/flight-factory)
[![Total Downloads](https://img.shields.io/packagist/dt/enlivenapp/flight-factory?style=for-the-badge)](https://packagist.org/packages/enlivenapp/flight-factory)
[![GitHub Issues](https://img.shields.io/github/issues/enlivenapp/FlightPHP-Factory?style=for-the-badge)](https://github.com/enlivenapp/FlightPHP-Factory/issues)
[![Contributors](https://img.shields.io/github/contributors/enlivenapp/FlightPHP-Factory?style=for-the-badge)](https://github.com/enlivenapp/FlightPHP-Factory/graphs/contributors)
[![Latest Release](https://img.shields.io/github/v/release/enlivenapp/FlightPHP-Factory?style=for-the-badge)](https://github.com/enlivenapp/FlightPHP-Factory/releases)
[![Contributions Welcome](https://img.shields.io/badge/contributions-welcome-blue?style=for-the-badge)](https://github.com/enlivenapp/FlightPHP-Factory/pulls)

# Flight Factory

**I noticed folks downloading some of these packages. I'm super grateful, Thank You!  I would like to let folks know until this notice disappears I'm doing a lot of breaking changes without worrying about them.  Once versions are up around 0.5.x things should settle down.**

Interactive scaffolding tool for FlightPHP applications and plugins.

## Installation

```bash
composer require enlivenapp/flight-factory
```

Commands are automatically available via `php runway build`.

## Commands

| Command | Description |
|---------|-------------|
| `build:app` | Create components for your application |
| `build:vendor` | Create components for a vendor package |

## Available Components

| Component | Description |
|-----------|-------------|
| `command` | CLI command |
| `config` | Configuration file |
| `controller` | Web or API controller |
| `entity` | ActiveRecord entity (src/Entities/) |
| `middleware` | Middleware class |
| `migration` | Database migration (PHP class) |
| `model` | ActiveRecord model (src/Models/) |
| `mvc` | Controller + Model + View combo |
| `repository` | Repository class (src/Repositories/) |
| `seed` | Database seeder |
| `service` | Service class |
| `util` | Utility class |
| `view` | View template |

## Usage

### Interactive Mode

Run without arguments to be prompted for everything:

```bash
php runway build:app
php runway build:vendor
```

### Direct Mode

Pass arguments to skip prompts:

```bash
php runway build:app controller UserController
php runway build:app controller UserController --type=api
php runway build:app mvc Blog
php runway build:vendor enlivenapp/my-plugin controller UserController
php runway build:vendor enlivenapp/my-plugin mvc Blog --type=api
```

### Creating a New Vendor Package

If the package doesn't exist, `build:vendor` will scaffold it for you. Flight School plugins get full boilerplate: `Plugin.php`, `Config/Config.php`, `Config/Routes.php`, and `Config/AdminRoutes.php`.

```bash
php runway build:vendor enlivenapp/my-new-plugin
```

## Warnings

Generally we trust Developers know what they're doing, we offer a warning when approching a potentially dangerous operation, but we don't block any operation. This will allow you to add to other authors' packages which could improve or break it but would be overwritten on a composer update. 

New packages can optionally be created as [Flight School](https://github.com/enlivenapp/FlightPHP-Flight-School) plugins. Warns if Flight School not installed. **will crash your install if you create a flight school plugin and flight school is not installed.**

### Help

```bash
php runway build
php runway build:app --help
php runway build:vendor --help
```

## Directory Structure

### build:app

Files are placed in the `app/` directory with lowercase directory names (use with skeleton):

```
app/commands/
app/config/
app/controllers/
app/entities/
app/middlewares/
app/migrations/
app/models/
app/repositories/
app/seeds/
app/services/
app/utils/
app/views/
```

### build:vendor

Files are placed in the package's `src/` directory with uppercase directory names (except `commands/`):

```
src/commands/
src/Config/           (Config.php, Routes.php, AdminRoutes.php)
src/Controllers/
src/Database/Migrations/
src/Entities/
src/Middlewares/
src/Models/
src/Repositories/
src/Seeds/
src/Services/
src/Utils/
src/Views/
```

## License

MIT
