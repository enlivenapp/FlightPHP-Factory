[![Version](http://poser.pugx.org/enlivenapp/flight-factory/version)](https://packagist.org/packages/enlivenapp/flight-factory)
[![License](http://poser.pugx.org/enlivenapp/flight-factory/license)](https://packagist.org/packages/enlivenapp/flight-factory)
[![Suggesters](http://poser.pugx.org/enlivenapp/flight-factory/suggesters)](https://packagist.org/packages/enlivenapp/flight-factory)
[![PHP Version Require](http://poser.pugx.org/enlivenapp/flight-factory/require/php)](https://packagist.org/packages/enlivenapp/flight-factory)
[![Monthly Downloads](https://poser.pugx.org/enlivenapp/flight-factory/d/monthly)](https://packagist.org/packages/enlivenapp/flight-factory)

# Flight Factory

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
| `middleware` | Middleware class |
| `migration` | Database migration |
| `model` | Model class |
| `mvc` | Controller + Model + View combo |
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

If the package doesn't exist, `build:vendor` will scaffold it for you:

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
app/middlewares/
app/migrations/
app/models/
app/seeds/
app/services/
app/utils/
app/views/
```

### build:vendor

Files are placed in the package's `src/` directory with uppercase directory names (except `commands/`):

```
src/commands/
src/Config/
src/Controllers/
src/Middlewares/
src/Migrations/
src/Models/
src/Seeds/
src/Services/
src/Utils/
src/Views/
```

## License

MIT
