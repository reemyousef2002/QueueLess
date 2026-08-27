# QueueLess — Digital Queue Management

QueueLess turns physical lines at clinics, bakeries, water points, community kitchens, university offices, and government offices into a live ticket that people can track from anywhere.

This repository contains the **frontend application**. The backend is planned to be built with **Laravel** and will expose a REST API that the frontend consumes.

---

## What it does

- **Landing page** — explains the product and lets visitors join a queue or learn more.
- **Authentication** — users can sign up / log in before joining a queue.
- **Service discovery** — browse nearby services, see live resource status, crowd density, and estimated wait times.
- **Join queue** — pick a service, choose priority options, and receive a digital ticket.
- **Live tracking** — watch your place in line, see how many people are ahead, and get an estimated wait time.

---

## Frontend

The frontend is a modern single-page application built with:

- **TanStack Start** — full-stack React framework with file-based routing and server functions.
- **React 19** — UI library.
- **TypeScript** — typed JavaScript for safer code.
- **Tailwind CSS v4** — utility-first styling with custom design tokens.
- **TanStack Query** — server-state management and data fetching.
- **Lucide React** — icon set.

### Project structure

```
src/
├── features/queueless/          # QueueLess feature modules
│   ├── components/              # Reusable UI components
│   ├── data.ts                  # Mock data and type definitions
│   └── store.tsx                # React Context state for selected org / ticket
├── routes/                      # TanStack Start file-based routes
│   ├── __root.tsx               # Root layout and providers
│   ├── index.tsx                # Landing page
│   ├── auth.tsx                 # Login / signup
│   ├── discovery.tsx            # Service discovery list
│   ├── join.tsx                 # Join queue confirmation
│   └── tracking.tsx             # Live ticket tracking
├── styles.css                   # Global styles and design tokens
├── router.tsx                   # Router configuration
└── start.ts                     # Start server configuration
```

### Run the frontend locally

```bash
# Install dependencies
bun install

# Start the development server
bun run dev
```

The app will be available at `http://localhost:8080`.

---

## Backend (planned: Laravel)

The backend will be a separate **Laravel** application that provides a REST API for QueueLess. It will handle:

- User authentication and authorization (Sanctum or JWT tokens).
- Organization / service management.
- Queue logic: ticket generation, position tracking, priority handling.
- Real-time status updates (optional: Laravel Echo + Pusher / Reverb for live wait times).
- Admin dashboard for service providers.

### Suggested Laravel stack

- **Laravel 11+** — PHP framework.
- **Laravel Sanctum** — API token authentication for the mobile/web frontend.
- **MySQL / PostgreSQL** — primary database.
- **Redis** — caching and queue driver.
- **Laravel Echo + Reverb / Pusher** — real-time updates for ticket tracking.
- **Laravel Scout** (optional) — search for services.

### Suggested API endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/register` | Register a new user |
| POST | `/api/login` | Authenticate a user |
| POST | `/api/logout` | Revoke user token |
| GET | `/api/organizations` | List all services |
| GET | `/api/organizations/{id}` | Get service details |
| POST | `/api/tickets` | Join a queue |
| GET | `/api/tickets/{code}` | Track a ticket |
| DELETE | `/api/tickets/{code}` | Cancel a ticket |
| GET | `/api/tickets/{code}/status` | Poll live queue status |

### Frontend ↔ Backend communication

The frontend will communicate with Laravel via `fetch` requests:

- Include the bearer token in the `Authorization` header after login.
- Use TanStack Query for caching, background refetching, and optimistic updates.
- Replace the mock data in `src/features/queueless/data.ts` with API calls.
- Replace the React Context ticket state with server-state managed by TanStack Query.

Example environment variable:

```env
VITE_API_BASE_URL=https://your-laravel-app.test/api
```

---

## Environment variables

Create a `.env` file in the frontend root if needed:

```env
VITE_API_BASE_URL=http://localhost:8000/api
```

The Laravel backend should have its own `.env` file with database, Redis, and Sanctum credentials.

---

## Deployment

- **Frontend**: deployable to any static/edge host compatible with TanStack Start (e.g. Lovable Cloud, Vercel, Cloudflare Pages).
- **Backend**: deploy the Laravel application to a PHP host such as Laravel Forge, Ploi, or a VPS with PHP-FPM + Nginx.

---

## Next steps

1. Set up the Laravel repository and configure authentication.
2. Create migrations for `users`, `organizations`, `queues`, and `tickets`.
3. Implement the API endpoints listed above.
4. Update the frontend to call the Laravel API instead of using mock data.
5. Add real-time updates for ticket tracking.

---

Built with TanStack Start, React, TypeScript, Tailwind CSS, and planned Laravel backend.
