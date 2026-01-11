# FLL 2026 Backend
Passende .env Datei wird ebenfalls in der directory benötigt

## Deployment
> [!IMPORTANT]
> Dies muss nur einmal ausgeführt werden!
```bash
chmod +x create-db.sh
./create-db.sh
```

Und dann:
```bash
php -S localhost:8000 -t public
```