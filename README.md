# Laravel Project Setup

## Prerequisites

Ensure you have the following installed:

- PHP >= 8.2
- Composer
- Node.js & npm (for compiling assets)

## Installation

1. **Install PHP dependencies:**
    ```sh
    composer install
    ```

2. **Install JavaScript dependencies:**
    ```sh
    npm install
    ```

3. **Copy the `.env.example` file to `.env`:**
    ```sh
    cp .env.example .env
    ```

4. **Generate the application key:**
    ```sh
    php artisan key:generate
    ```

5. **Set up the database:**
    - Update the `.env` file with your database credentials.
    - Run the migrations:
    ```sh
    php artisan migrate
    ```

6. **Run the development server:**
    ```sh
    php artisan serve
    ```

7. **Compile the assets:**
    ```sh
    npm run dev
    ```

## Additional Commands

- **Optimize the application:**
    ```sh
    php artisan optimize
    ```

- **Optimize clear the application:**
    ```sh
    php artisan optimize:clear
    ```

- **Database seeding:**
    ```sh
    php artisan db:seed
    ```

- **Permission Seeding:**
    ```sh
    php artisan db:seed --class=PermissionSeeder
    ```

- **Run tests:**
    ```sh
    php artisan test
    ```

- **Clear application cache:**
    ```sh
    php artisan cache:clear
    ```

- **Clear configuration cache:**
    ```sh
    php artisan config:clear
    ```

- **Clear route cache:**
    ```sh
    php artisan route:clear
    ```

- **Clear view cache:**
    ```sh
    php artisan view:clear
    ```
- **Storage Connection:**
    ```sh
    php artisan storage:link
    ```

- **Queue Worker Run:**
    ```sh
    php artisan queue:work
    ```
- **Module Spcific Seeder:**
    ```sh
    php artisan module:seed --class=ClassName ModuleName
    ```

- **Get All Route List:**
    ```sh
    php artisan route:list
    ```

- **Run with Tinker:**
    ```sh
    (new \Modules\Settings\database\seeders\SeederName)->run();
    ```
- **Dispatch Job with Tinker:**
    ```sh
    App\Jobs\JobName::dispatch();
    ```
