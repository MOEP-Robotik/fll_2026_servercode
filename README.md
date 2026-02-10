# FLL 2026 Backend
## Requirements
- `intl` Plugin
- `pdo_sqlite` Plugin
- `imagick` Plugin

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

## Frontend

Das Frontend kann man [hier](https://github.com/MOEP-Robotik/Forschungsprojekt_fll_2026) finden!
