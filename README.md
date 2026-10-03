# Tweety

Tweety is the backend for a social networking platform, built with Laravel. It provides a versioned `v1` REST API for accounts, profiles, posts, interactions, and conversations, with token-based authentication and support for real-time events.

## What the Project Does

Tweety provides the core services behind a social network. People can create an account, set up a profile, find other users, and follow accounts they want to keep up with. After signing in, a user can publish text or image posts and browse a paginated timeline containing their own posts and posts from followed users. Reposts also appear in the timeline, ordered by when the post or repost was created.

The API supports common conversations around posts: users can like or bookmark a post, repost it or add a quote, and leave comments with replies. Following, post interactions, and other activity can produce notifications. For private communication, users can create conversations, exchange messages and attachments, see unread counts, and send typing updates. Account and profile endpoints also cover email verification, password recovery, and profile management.


## Features

- User registration, login, and logout with Laravel Sanctum.
- Google sign-in, email verification, and OTP-based password recovery.
- User profiles with usernames and account details, plus follow and unfollow actions.
- Create, update, and delete posts, attach images, and view the feed.
- Like, bookmark, repost, and quote posts.
- Comments and nested replies.
- Search for users, block them, and unblock them.
- Notifications and unread notification counts.
- Private conversations and messages, message attachments, typing status, and unread message counts.
- Broadcast events and channels for conversations, notifications, and updates. Broadcasting defaults to the application log; live broadcasting requires Reverb or another compatible provider to be configured.

## Tech Stack

- PHP `^8.2` and Laravel `^12.0`.
- MySQL by default, as configured in `.env.example`.
- Laravel Sanctum for authentication and API tokens.
- Laravel Reverb for WebSocket broadcasting when enabled.
- Laravel Socialite for Google sign-in.
- Pest 3 and PHPUnit for testing.
- Scramble for API documentation.

## Requirements

- PHP 8.2 or later, with the extensions required by Laravel.
- Composer.
- MySQL.
- A local server environment such as XAMPP on Windows, or an equivalent PHP environment.

## Installation on Windows

1. Clone the repository and move into the project directory:

   ```powershell
   git clone https://github.com/Hagar-Elbakry/Tweety.git
   cd Tweety
   ```

2. Start MySQL from XAMPP and create two empty databases: `tweety` for the application and `tweety_test` for tests.

3. Create the environment file if it does not exist, then configure the database settings in `.env`:

   ```powershell
   if (-not (Test-Path .env)) { Copy-Item .env.example .env }
   ```

   The defaults in `.env.example` use `127.0.0.1:3306`, the `tweety` database, and the `root` user with no password. Update these values to match your MySQL setup.

4. Install the PHP dependencies, generate the application key, and run the database migrations:

   ```powershell
   composer install
   php artisan key:generate
   php artisan migrate
   ```

5. Create the public storage link when using post images or user files:

   ```powershell
   php artisan storage:link
   ```

6. Start the application:

   ```powershell
   php artisan serve
   ```

   The application is usually available at `http://127.0.0.1:8000`, and the API base URL is `http://127.0.0.1:8000/api/v1`.

   If you need to process queued jobs, open another terminal and run:

      ```powershell
      php artisan queue:listen
      ```


## Optional Service Configuration

### Email

`.env.example` sets `MAIL_MAILER=log`, so verification, welcome, and password reset emails are written to the application log instead of being sent. To send real email, configure the `MAIL_*` variables in `.env` for your mail provider.

### Google OAuth

To enable Google sign-in, add your OAuth credentials to `.env`:

```dotenv
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=http://127.0.0.1:8000/api/v1/google/callback
```

The callback URL must also be registered in your Google OAuth application settings.

### WebSockets and Reverb

The default is `BROADCAST_CONNECTION=log`, which is suitable for development without a WebSocket server. To enable Reverb, set `BROADCAST_CONNECTION=reverb` and configure the `REVERB_*` variables in `.env`, then run the Reverb server in a separate terminal:

```powershell
php artisan reverb:start
```


## API

All routes below are prefixed with `/api/v1`. Routes not listed under **Public Routes** require a Sanctum token, usually sent in the `Authorization: Bearer <token>` header. The API accepts and returns JSON.

### Public Routes

| Method | Path | Purpose |
|---|---|---|
| `POST` | `/register` | Register an account |
| `POST` | `/login` | Log in and receive a token |
| `GET` | `/google/redirect` | Start Google sign-in |
| `GET` | `/google/callback` | Handle the Google sign-in response |
| `POST` | `/forget-password` | Request an OTP for password recovery |
| `POST` | `/verify-otp` | Verify an OTP |
| `GET` | `/profile/{username}` | View a public profile |

### Authenticated Routes

| Group | Available routes |
|---|---|
| Account | `POST /logout`, `POST /email/verify`, `POST /email/verify/resend`, `POST /reset-password` |
| Profile | `GET /profile/me`, `PATCH /profile`, `DELETE /profile` |
| Posts | `POST /posts`, `PATCH /posts/{post}`, `DELETE /posts/{post}`, `GET /feed` |
| Post interactions | `POST /posts/{post}/like`, `POST /posts/{post}/bookmark`, `POST /posts/{post}/repost`, `DELETE /posts/{post}/repost`, `POST /posts/{post}/quote` |
| Comments | `GET /posts/{post}/comments`, `POST /posts/{post}/comments`, `DELETE /comments/{comment}`, `GET /comments/{comment}/replies` |
| Following, search, and blocking | `POST /follow`, `GET /users/search`, `POST /users/{user}/block`, `DELETE /users/{user}/block` |
| Notifications | `GET /notifications`, `GET /notifications/unread-count` |
| Conversations | `GET /conversations`, `POST /conversations`, `GET /conversations/unread-count`, `GET /conversations/{conversation}/messages`, `POST /conversations/{conversation}/messages` |
| Message management | `PATCH /conversations/{conversation}/messages/{message}`, `DELETE /conversations/{conversation}/messages/{message}`, `POST /conversations/{conversation}/typing` |

Registration and login return a token with the user data. The `/reset-password` route requires a token with the `reset-password` ability, while requesting and verifying an OTP do not require authentication.

### Login Example

```http
POST /api/v1/login
Content-Type: application/json
Accept: application/json

{
  "email": "user@example.com",
  "password": "your-password"
}
```

Use the `token` value from the response for authenticated requests:

```http
Authorization: Bearer YOUR_TOKEN
Accept: application/json
```

Validation errors normally return HTTP `422`, and missing resources return `404`.

## Tests

Run the test suite with:

```powershell
php artisan test
```

The PHPUnit configuration in `phpunit.xml` uses MySQL and a database named `tweety_test`. Make sure it exists and that MySQL is available before running tests; keep it separate from your development data.

## Project Structure

```text
app/
  Actions/          Application actions
  Events/           Application and broadcast events
  Http/             Controllers, Requests, Resources, and Middleware
  Listeners/        Event listeners
  Mail/             Email classes
  Models/           Eloquent models
  Notifications/    Laravel notifications
  Services/         Business logic services
database/
  factories/        Test data factories
  migrations/       Database schema changes
  seeders/          Initial data
resources/
  css/ js/ views/   Vite assets and Blade templates
routes/
  api.php           Versioned v1 API routes
  channels.php      Private broadcast channels
tests/
  Feature/ Unit/    Feature and unit tests
```

## Documentation

Scramble is included as a project dependency for generating API documentation. You can also inspect the registered API routes and their names with:

```powershell
php artisan route:list --path=api/v1
```
