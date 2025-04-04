# Chat Application Backend

A simple chat application backend written in PHP using the Slim framework. It allows users to create chat groups, join these groups, and send messages within them.

## Features

- Create chat groups
- Join existing chat groups
- Send messages in chat groups
- List messages in chat groups
- Token-based authentication
- SQLite database storage
- RESTful JSON API

## Requirements

- PHP 7.4 or higher
- Composer
- SQLite extension for PHP

## Installation

1. Clone the repository:

```bash
git clone https://github.com/your-username/chat-application.git
cd chat-application
```

2. Install dependencies:

```bash
composer install
```

This will create a `vendor` directory containing all the required dependencies as specified in `composer.json`.

3. Populate the database with sample data (optional):

```bash
php bin/init_db.php
```

This script will insert sample users, groups, and messages into your database to get you started.

## Deployment Notes

When deploying this project:

1. **Don't commit the `vendor` directory**: The `.gitignore` file is configured to exclude it.
2. **Dependencies installation**: After cloning the repository on a new system, always run `composer install` to install dependencies.
3. **Database files**: SQLite database files (*.sqlite) are excluded from version control to keep the repository clean. The application will create the database file automatically when needed.
4. **Server configuration**: If deploying to a production server, configure your web server (Apache, Nginx) to point to the `public` directory for better security.

## Configuration

The application uses a SQLite database file located at the project root (`database.sqlite` by default). You can customize the database path in your application by using the `Database` class:

```php
use App\Database\Database;

// Set a custom database path
Database::setDbPath('/your/custom/path/chat.sqlite');
```

## Running the Application

You can use PHP's built-in server for development:

```bash
php -S localhost:8000
```

Then access the API endpoints at `http://localhost:8000/`.

## Authentication

All endpoints except `GET /groups` require authentication. Authentication is performed using a token that is assigned to each user. You must include this token in your requests.

To authenticate your requests, include the token in the request body:

```json
{
  "token": "user_auth_token_here"
}
```

## API Endpoints

### Get All Groups (No Authentication Required)

```
GET /groups
```

Response example:
```json
[
  {
    "id": 1,
    "name": "General",
    "creator_id": 1,
    "created_at": "2023-05-01 12:00:00"
  },
  {
    "id": 2,
    "name": "Random",
    "creator_id": 1,
    "created_at": "2023-05-01 12:30:00"
  }
]
```

### Create a Group

```
POST /groups
```

Request body:
```json
{
  "name": "New Group",
  "token": "user_auth_token_here"
}
```

Response example:
```json
{
  "message": "Group created successfully",
  "group": {
    "id": 3,
    "name": "New Group",
    "creator_id": 1,
    "created_at": "2023-05-01 13:00:00"
  }
}
```

### Join a Group

```
POST /groups/{group_id}/join
```

Request body:
```json
{
  "token": "user_auth_token_here"
}
```

Response example:
```json
{
  "message": "Joined group successfully",
  "group_id": 3,
  "user": {
    "id": 2,
    "username": "janedoe"
  }
}
```

### Send a Message

```
POST /groups/{group_id}/messages
```

Request body:
```json
{
  "token": "user_auth_token_here",
  "content": "Hello, world!"
}
```

Response example:
```json
{
  "message": "Message sent successfully",
  "data": {
    "id": 1,
    "group_id": 3,
    "user_id": 1,
    "content": "Hello, world!",
    "created_at": "2023-05-01 13:15:00",
    "username": "johndoe"
  }
}
```

### Get Messages from a Group

```
GET /groups/{group_id}/messages
```

Query parameters:
- `token` - User authentication token
- `limit` (optional, default: 50) - Number of messages to return
- `offset` (optional, default: 0) - Offset for pagination

Example request:
```
GET /groups/3/messages?token=user_auth_token_here&limit=10&offset=0
```

Response example:
```json
{
  "group_id": 3,
  "messages": [
    {
      "id": 1,
      "group_id": 3,
      "user_id": 1,
      "content": "Hello, world!",
      "created_at": "2023-05-01 13:15:00",
      "username": "johndoe"
    },
    {
      "id": 2,
      "group_id": 3,
      "user_id": 2,
      "content": "Hi there!",
      "created_at": "2023-05-01 13:16:00",
      "username": "janedoe"
    }
  ],
  "pagination": {
    "limit": 10,
    "offset": 0
  }
}
```

## User Token Management

When a user is created, they are assigned a unique token. This token is used for authentication in all API requests that require it.

### Getting a User Token

For a new user, you can get their token by creating them through the API. For existing users, you'll need to retrieve their token from the database.

Example of token generation in the User model:
```php
// Create a new user with a random token
$token = bin2hex(random_bytes(16));

$stmt = $this->db->prepare("
    INSERT INTO users (username, token) 
    VALUES (:username, :token)
");
```

## Tests

The project includes a comprehensive suite of tests to verify the functionality of the models, database, and authentication system.

### Available Tests

1. **DatabaseTest.php**: Tests the Database class configuration, initialization and schema
2. **UserTest.php**: Tests user creation and token-based authentication
3. **GroupTest.php**: Tests group creation, retrieval, and membership operations
4. **AuthenticationTest.php**: Integration tests for token-based authentication across endpoints

### Running Tests

#### Running Individual Tests

You can run individual test files like this:

```bash
php tests/DatabaseTest.php
php tests/UserTest.php
php tests/GroupTest.php
```

The `AuthenticationTest.php` requires a running server:

```bash
# In one terminal, start the server
php -S localhost:8000

# In another terminal, run the authentication tests
php tests/AuthenticationTest.php
```

#### Running All Tests

To run all model and database tests at once:

```bash
php tests/run_tests.php
```

### What the Tests Verify

- **DatabaseTest**: Verifies that the Database class correctly initializes the SQLite database, creates all required tables, and implements the singleton pattern. It also checks for the presence of the token column in the users table.

- **UserTest**: Verifies that users can be created with auto-generated tokens, retrieved by their tokens, and that invalid tokens are properly rejected.

- **GroupTest**: Verifies that groups can be created, retrieved, and that users can join groups and be listed as members.

- **AuthenticationTest**: Tests the full authentication flow with API endpoints, ensuring that valid tokens can access protected resources and invalid tokens are rejected.

### Test Database

The tests use a separate SQLite database file (`test.sqlite`) to avoid affecting your production data. Each test cleans the database before running to ensure a consistent environment.

## Running the Tests

The test suite uses a separate test database to ensure it doesn't affect your production data.

Run the tests using the provided script:

```bash
php tests/run_tests.php
```

## Working with the SQLite Database

If you want to directly query the SQLite database, you can use the SQLite command-line tool:

```bash
sqlite3 database.sqlite
```

This will open the SQLite shell where you can run SQL queries. Some useful commands:

```sql
-- List all tables
.tables

-- Show table schema
.schema users
.schema groups

-- View user tokens
SELECT username, token FROM users;
```

## Project Structure

```
/my-chat-app
    index.php          # Front controller for Slim
    config.php         # Configuration file
    database.sqlite    # SQLite database file
  /src
    /Controllers       # Request handlers for API endpoints
      GroupController.php
      MessageController.php
    /Models            # Classes for Groups, Messages, Memberships
      Group.php
      Message.php
      Membership.php
      User.php
    /Database          # Database connection and query utilities
      Database.php
  /bin
    init_db.php        # Database initialization script
  /tests               # Test files
    run_tests.php      # Script to run all tests
    DatabaseTest.php   # Tests for Database class
    UserTest.php       # Tests for User model
    GroupTest.php      # Tests for Group model
    AuthenticationTest.php # Integration tests for authentication
  composer.json        # Project dependencies
  README.md            # Project overview and instructions
```

## License

MIT