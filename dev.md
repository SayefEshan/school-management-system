
# Development Notes — School Management System

## IDE Helper

```bash
php artisan ide-helper:generate
php artisan ide-helper:meta
php artisan ide-helper:models
```

## Steps After Creating a New Module

### 1. Create the module

```bash
php artisan module:make ModuleName
```

### 2. Dump autoload and migrate

```bash
composer dump-autoload
php artisan migrate
```

### 3. Register permissions (if applicable)

Create a permission seeder in the module and add it to `database/seeders/PermissionSeeder.php`.
