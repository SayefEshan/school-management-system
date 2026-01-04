

# Laravel IDE Helper

```bash
php artisan ide-helper:generate
php artisan ide-helper:meta
php artisan ide-helper:models
```

# Steps after creating a new module

### 1. Create the module

```bash
php artisan make:module RolePermission
```

### 2. Run Dump Autoload

```bash
composer dump-autoload
```

```bash
php artisan migrate
```

