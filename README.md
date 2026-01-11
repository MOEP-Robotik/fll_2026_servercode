# FLL 2026 Backend
## Deployment
> [!IMPORTANT]
> Dies muss nur einmal ausgeführt werden!
```bash
chmod +x create-db.sh
./create-db.sh
```

Danach muss die `.env` datei angepasst werden. Einfach die Datei `.env.example` kopieren und in `.env` umbenennen. Danach die Zugangsdaten anpassen.

Und dann:
```bash
php -S localhost:8000 -t public
```

Fertig! Die API kann nun aufgerufen werden unter `http://localhost:8000`. Nur sehr uninteressant ohne frontend...

## Routen
- `GET /api/health`
- `GET /api/submissions`
- `POST /api/submissions`
- `GET /api/submissions/{id}`

## Ein Fund beitragen
```bash
curl -X POST http://localhost:8000/api/submissions \
            -H "Content-Type: application/json" \
            -d '{
          "title": "Name des Funds",
          "description": "Wo gefunden?",
          "location": "Köln",
          "email": "example@example.com"
        }'
```