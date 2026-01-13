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

## Einen Fund beitragen
```bash
curl -X POST http://localhost:8000/api/submissions \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Bronze fibula 5",
    "description": "Found near river bank during construction work",
    "coordinate": {
      "lon": 6.9603,
      "lat": 50.9375
    },
    "email": "example@example.com"
  }'
```