# Tasks API (backend)

Backend zadań w Symfony 7 (PHP 8.2+). 
Lokalne uruchomienie z frontendem React (osobno).

## Wymagania

- PHP 8.2+, Composer
- **Baza: SQLite** – w `php.ini` włącz rozszerzenie: `extension=pdo_sqlite`  i `extension=sqlite3`
- Opcjonalnie: Symfony CLI (`symfony serve`)

## Uruchomienie

### 1. Zależności

```bash
composer install
lub (w przypadku problemów)
composer install --ignore-platform-reqs
```

### 2. Środowisko (.env)

```bash
cp .env.example .env
```

W `.env` ustaw m.in.:

```env
APP_ENV=dev

# SQLite
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"

# CORS – origin frontendu (React), np. Vite = localhost:5173
CORS_ALLOW_ORIGIN='^https?://(localhost|127\.0\.0\.1)(:[0-9]+)?$'
```

*(Jeśli frontend jest na innym porcie/hostcie, dostosuj regex w `CORS_ALLOW_ORIGIN` lub konfigurację w `config/packages/nelmio_cors.yaml`.)*

### 3. Baza i migracje

```bash
mkdir -p var
php bin/console doctrine:migrations:migrate --no-interaction
```

### 4. (Opcjonalnie) Dane testowe

```bash
php bin/console doctrine:fixtures:load --no-interaction
```

### 5. Serwer

```bash
symfony serve
# lub: php -S localhost:8000 -t public
```

API: **http://127.0.0.1:8000**. W React ustaw base URL na ten adres.

---

## API (skrót)

| Metoda | URL | Opis |
|--------|-----|------|
| GET | `/api/tasks` | Lista (`page`, `limit`, `status`, `search_keyword`) |
| POST | `/api/tasks` | Nowe zadanie |
| PUT | `/api/tasks/{id}` | Edycja |
| PATCH | `/api/tasks/{id}` | Zmiana statusu |
