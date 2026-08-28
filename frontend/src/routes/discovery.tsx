import { useMemo, useState } from "react";
import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { Bell } from "lucide-react";

import { ORGS, TYPES } from "@/features/queueless/data";
import { OrgCard } from "@/features/queueless/components/OrgCard";
import { Logo } from "@/features/queueless/components/Primitives";
import { useQueue } from "@/features/queueless/store";

export const Route = createFileRoute("/discovery")({
  head: () => ({
    meta: [
      { title: "Find a service near you — QueueLess" },
      {
        name: "description",
        content: "Browse clinics, bakeries, water points, kitchens and offices with live queue and resource status.",
      },
      { property: "og:title", content: "Service discovery — QueueLess" },
      { property: "og:description", content: "Check resource availability and crowd levels before you join a queue." },
    ],
  }),
  component: Discovery,
});

function Discovery() {
  const navigate = useNavigate();
  const { setSelectedOrg, ticket, peopleAhead } = useQueue();
  const [filter, setFilter] = useState("all");

  const filtered = useMemo(
    () => (filter === "all" ? ORGS : ORGS.filter((o) => o.type === filter)),
    [filter],
  );
  const chips = [
    { id: "all", label: "All" },
    ...Object.entries(TYPES).map(([id, t]) => ({ id, label: t.label })),
  ];

  return (
    <div className="min-h-screen bg-background font-sans">
      <header className="border-b border-border bg-card">
        <div className="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
          <Logo />
          <div className="flex items-center gap-3">
            <button
              type="button"
              aria-label="Notifications"
              className="grid h-9 w-9 place-items-center rounded-xl border border-border text-muted-foreground transition hover:bg-muted hover:text-foreground"
            >
              <Bell className="h-4 w-4" />
            </button>
            <button
              type="button"
              onClick={() => navigate({ to: "/" })}
              className="text-sm text-muted-foreground transition hover:text-foreground"
            >
              Log out
            </button>
          </div>
        </div>
      </header>

      <main className="mx-auto max-w-6xl px-6 py-10">
        <h1 className="font-display text-3xl font-bold tracking-tight text-foreground">
          Find a service near you
        </h1>
        <p className="mt-2 text-sm text-muted-foreground">
          Check resource availability and crowd levels before you join a queue.
        </p>

        {ticket && (
          <div className="mt-6 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-border bg-card p-4 shadow-soft">
            <div>
              <p className="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                Active ticket
              </p>
              <p className="mt-1 text-sm font-semibold text-foreground">
                <span className="font-mono">{ticket.code}</span> · {ticket.org.name} ·{" "}
                {peopleAhead === 0 ? "It's your turn" : `${peopleAhead} ahead`}
              </p>
            </div>
            <button
              type="button"
              onClick={() => navigate({ to: "/tracking" })}
              className="rounded-xl bg-ink px-4 py-2 text-sm font-semibold text-ink-foreground transition hover:opacity-90"
            >
              Track my queue
            </button>
          </div>
        )}

        <div className="mt-6 flex flex-wrap gap-2">
          {chips.map((c) => (
            <button
              key={c.id}
              type="button"
              onClick={() => setFilter(c.id)}
              className={`rounded-full px-4 py-1.5 text-sm font-medium transition ${
                filter === c.id
                  ? "bg-ink text-ink-foreground"
                  : "border border-border bg-card text-muted-foreground hover:bg-muted"
              }`}
            >
              {c.label}
            </button>
          ))}
        </div>

        <div className="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          {filtered.map((org) => (
            <OrgCard
              key={org.id}
              org={org}
              joined={ticket?.org.id === org.id}
              blocked={Boolean(ticket) && ticket?.org.id !== org.id}
              onView={() => navigate({ to: "/tracking" })}
              onJoin={(o) => {
                setSelectedOrg(o);
                navigate({ to: "/join" });
              }}
            />
          ))}
        </div>
      </main>
    </div>
  );
}
