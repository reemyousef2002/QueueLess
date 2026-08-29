# QueueLess — Digital Queue Management

QueueLess turns physical lines at clinics, bakeries, water points, community kitchens, university offices, and government offices into a live ticket that people can track from anywhere.

This repository contains both halves of the app: [`frontend/`](frontend) (TanStack Start + React) and [`backend/`](backend) (Laravel REST API).

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

## Backend (Laravel)

The backend is a **Laravel 13** REST API implementing the System Analysis
Document's 19 functional requirements: Sanctum token auth, the 10-entity
database design, all documented endpoints, and the queue/notification
business logic behind them (ticket generation, position tracking, priority
handling, resource-status and crowd-density reporting, staff/admin
dashboards, and the public "Now Serving" display).

See **[`backend/README.md`](backend/README.md)** for the full stack,
setup instructions, and seeded test accounts.

### Frontend ↔ Backend communication

The frontend will communicate with Laravel via `fetch` requests:

- Include the bearer token in the `Authorization` header after login.
- Use TanStack Query for caching, background refetching, and optimistic updates.

Set the API's base URL in `frontend/.env`:

```env
VITE_API_URL=http://localhost:8002/api
```

---

## Environment variables

- **Frontend** (`frontend/.env`): `VITE_API_URL` — the Laravel API's base URL.
- **Backend** (`backend/.env`, gitignored — copy from `backend/.env.example`): database credentials, `APP_URL`, `APP_KEY`. See [`backend/README.md`](backend/README.md) for setup.

---

## Deployment

- **Frontend**: deployable to any static/edge host compatible with TanStack Start (e.g. Lovable Cloud, Vercel, Cloudflare Pages).
- **Backend**: deploy the Laravel application to a PHP host such as Laravel Forge, Ploi, or a VPS with PHP-FPM + Nginx.

---

Built with TanStack Start, React, TypeScript, Tailwind CSS — and Laravel 13.
