#!/bin/bash
set -e

# --- CONFIG ---
PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
DB_DIR="$PROJECT_ROOT/database"
DB_FILE="$DB_DIR/db.sqlite"

echo "Initializing database..."
echo "Project root: $PROJECT_ROOT"

# --- CREATE DIRECTORY ---
if [ ! -d "$DB_DIR" ]; then
  echo "Creating database directory"
  mkdir -p "$DB_DIR"
fi

# --- CREATE DB FILE ---
if [ -f "$DB_FILE" ]; then
  rm -f "$DB_FILE"
  echo "Existing database file removed"
fi
echo "Creating database file"
touch "$DB_FILE"

# --- PERMISSIONS ---
echo "Setting permissions"
chown -R "$(whoami)":"$(whoami)" "$DB_DIR"
chmod 755 "$DB_DIR"
chmod 644 "$DB_FILE"

# --- CREATE TABLES ---
echo "Creating tables (if not exist)"

sqlite3 "$DB_FILE" <<'SQL'
CREATE TABLE IF NOT EXISTS submissions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    description TEXT,
    location TEXT,
    email TEXT,
    telephone TEXT,
    address TEXT,
    date TEXT,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS analysis (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    submission_id INTEGER NOT NULL,
    material TEXT,
    confidence REAL,
    notes TEXT,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (submission_id) REFERENCES submissions(id)
);
SQL

echo "Database initialized successfully"
