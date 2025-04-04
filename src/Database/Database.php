<?php
// src/Database/Database.php

namespace App\Database;

class Database {
    private static $instance = null;
    private $pdo;
    private static $dbPath = __DIR__ . '/../../database.sqlite';  // Default path

    /**
     * Set the database file path
     * 
     * @param string $path Path to SQLite database file
     */
    public static function setDbPath($path) {
        self::$dbPath = $path;
        self::$instance = null; // Reset instance to use new path
    }

    /**
     * Get the database connection instance, Singleton Pattern
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->pdo;
    }

    /**
     * Get the path to the SQLite database file
     * 
     * @return string The database file path
     */
    public static function getDbPath() {
        return self::$dbPath;
    }

    // Private constructor - creates new database connection
    private function __construct() {
        try {
            // Check if the directory exists
            $dbDir = dirname(self::$dbPath);
            if (!file_exists($dbDir)) {
                throw new \RuntimeException("Database directory path '{$dbDir}' does not exist. Please check your configuration.");
            }
            
            $this->pdo = new \PDO('sqlite:' . self::$dbPath);
            // Set error mode to exceptions
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            // Set default fetch mode
            $this->pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
            // Enable foreign keys
            $this->pdo->exec('PRAGMA foreign_keys = ON');
            
            // Initialize database if it doesn't exist
            $this->initializeDatabase();
        } catch (\PDOException $e) {
            die("Could not connect to the database: " . $e->getMessage());
        }
    }

    private function initializeDatabase() {
        // Check if tables already exist
        $stmt = $this->pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'");
        if ($stmt->fetch()) {
            return; // Tables already exist
        }

        // Create users table
        $this->pdo->exec("
            CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT NOT NULL UNIQUE,
                token TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Create groups table
        $this->pdo->exec("
            CREATE TABLE groups (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                creator_id INTEGER,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (creator_id) REFERENCES users(id)
            )
        ");

        // Create group memberships table
        $this->pdo->exec("
            CREATE TABLE group_memberships (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                group_id INTEGER NOT NULL,
                user_id INTEGER NOT NULL,
                joined_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                UNIQUE(group_id, user_id),
                FOREIGN KEY (group_id) REFERENCES groups(id),
                FOREIGN KEY (user_id) REFERENCES users(id)
            )
        ");

        // Create messages table
        $this->pdo->exec("
            CREATE TABLE messages (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                group_id INTEGER NOT NULL,
                user_id INTEGER NOT NULL,
                content TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (group_id) REFERENCES groups(id),
                FOREIGN KEY (user_id) REFERENCES users(id)
            )
        ");
    }
}
