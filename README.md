stack:
- php: 8.1
- node: 22.2.0
- npm: 10.7.0

instrukcja:
- utworzyć .env
- utworzyć db
- edytować zmienne dotyczące db w .env

w cmd:
- composer install
- php artisan key:generate
- php artisan optimize
- php artisan migrate

- npm i

dla szybkiego przetestowania:
- npm run dev
- php artisan serve --port=80