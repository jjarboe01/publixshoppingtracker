#!/usr/bin/env python3
"""
Migration script to add savings column to existing purchases table.
Run this once to update your existing database.
"""

import sqlite3
import os

def migrate_database(db_path="/app/data/publix_tracker.db"):
    """
    Add savings column to purchases table if it doesn't exist.
    
    Args:
        db_path: Path to the database file
    """
    if not os.path.exists(db_path):
        print(f"Database not found at {db_path}")
        print("Database will be created with the new schema when you run GetReciepts.py")
        return
    
    print(f"Migrating database: {db_path}")
    
    conn = sqlite3.connect(db_path)
    cursor = conn.cursor()
    
    try:
        # Check if savings column already exists
        cursor.execute("PRAGMA table_info(purchases)")
        columns = [row[1] for row in cursor.fetchall()]
        
        if 'savings' in columns:
            print("✓ Savings column already exists. No migration needed.")
        else:
            print("Adding savings column to purchases table...")
            cursor.execute('''
                ALTER TABLE purchases ADD COLUMN savings REAL DEFAULT 0.0
            ''')
            conn.commit()
            print("✓ Successfully added savings column!")
            
        # Show current schema
        print("\nCurrent purchases table schema:")
        cursor.execute("PRAGMA table_info(purchases)")
        for row in cursor.fetchall():
            col_id, name, col_type, not_null, default_val, pk = row
            nullable = "NOT NULL" if not_null else "NULL"
            default = f"DEFAULT {default_val}" if default_val is not None else ""
            pk_marker = "PRIMARY KEY" if pk else ""
            print(f"  {name:<20} {col_type:<10} {nullable:<10} {default:<15} {pk_marker}")
            
    except Exception as e:
        print(f"Error during migration: {e}")
        conn.rollback()
    finally:
        conn.close()
        print("\nMigration complete!")

if __name__ == "__main__":
    # Try multiple possible database locations
    possible_paths = [
        "/app/data/publix_tracker.db",  # Docker volume
        "data/publix_tracker.db",  # Local data directory
        "PublixTracker/data/publix_tracker.db"  # Relative path
    ]
    
    db_found = False
    for path in possible_paths:
        if os.path.exists(path):
            migrate_database(path)
            db_found = True
            break
    
    if not db_found:
        print("No existing database found. The new schema will be used when GetReciepts.py runs.")
