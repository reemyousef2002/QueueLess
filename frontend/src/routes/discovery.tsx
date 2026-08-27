import { useMemo, useState } from "react";
import { createFileRoute, useNavigate } from "@tanstack/react-router";

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
  const { setSelectedOrg } = useQueue();
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
          <button
            type="button"
            onClick={() => navigate({ to: "/" })}
            className="text-sm text-muted-foreground transition hover:text-foreground"
          >
            Log out
          </button>
        </div>
      </header>

      <main className="mx-auto max-w-6xl px-6 py-10">
        <h1 className="font-display text-3xl font-bold tracking-tight text-foreground">
          Find a service near you
        </h1>
        <p className="mt-2 text-sm text-muted-foreground">
          Check resource availability and crowd levels before you join a queue.
        </p>

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
