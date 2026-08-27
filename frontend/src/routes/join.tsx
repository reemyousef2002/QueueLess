import { useState } from "react";
import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { ArrowLeft, ShieldCheck } from "lucide-react";

import { TYPES } from "@/features/queueless/data";
import { Logo, PrimaryButton, StatusPill } from "@/features/queueless/components/Primitives";
import { useQueue } from "@/features/queueless/store";

export const Route = createFileRoute("/join")({
  head: () => ({
    meta: [
      { title: "Join the queue — QueueLess" },
      { name: "description", content: "Reserve your ticket remotely and see how many people are ahead of you." },
      { property: "og:title", content: "Join a queue — QueueLess" },
      { property: "og:description", content: "Get a ticket number and estimated wait without standing in line." },
    ],
  }),
  component: JoinPage,
});

function JoinPage() {
  const navigate = useNavigate();
  const { selectedOrg: org, setTicket } = useQueue();
  const [priority, setPriority] = useState(false);

  if (!org) {
    return (
      <div className="flex min-h-screen items-center justify-center bg-background font-sans">
        <PrimaryButton onClick={() => navigate({ to: "/discovery" })}>
          Choose a service first
        </PrimaryButton>
      </div>
    );
  }

  const type = TYPES[org.type];
  const peopleAhead = priority ? Math.max(1, Math.round(org.queueSize / 3)) : org.queueSize;
  const ticketNumber = `${org.prefix}-${100 + org.queueSize + 4}`;
  const estWait = peopleAhead * org.avgService;

  return (
    <div className="min-h-screen bg-background font-sans">
      <header className="border-b border-border bg-card">
        <div className="mx-auto flex max-w-2xl items-center justify-between px-6 py-4">
          <Logo />
          <button
            type="button"
            onClick={() => navigate({ to: "/discovery" })}
            className="flex items-center gap-1.5 text-sm text-muted-foreground transition hover:text-foreground"
          >
            <ArrowLeft className="h-4 w-4" /> Back to services
          </button>
        </div>
      </header>

      <main className="mx-auto max-w-2xl px-6 py-10">
        <div className="overflow-hidden rounded-2xl border border-border bg-card shadow-soft">
          <img
            src={type.image}
            alt={org.name}
            loading="lazy"
            className="aspect-[16/7] w-full object-cover"
          />
          <div className="flex items-center justify-between gap-3 p-5">
            <div>
              <h1 className="font-display text-xl font-bold text-foreground">{org.name}</h1>
              <p className="text-sm text-muted-foreground">{type.label}</p>
            </div>
            <StatusPill status={org.status} />
          </div>
        </div>

        <div className="mt-6 rounded-2xl bg-ink p-6 text-ink-foreground">
          <p className="text-xs font-semibold uppercase tracking-widest text-brand">Your ticket</p>
          <p className="mt-2 font-mono text-5xl font-bold">{ticketNumber}</p>
          <div className="mt-6 grid grid-cols-2 gap-4">
            <div className="rounded-xl bg-hairline p-4">
              <p className="text-xs text-ink-muted">People ahead</p>
              <p className="font-mono text-2xl font-bold">{peopleAhead}</p>
            </div>
            <div className="rounded-xl bg-hairline p-4">
              <p className="text-xs text-ink-muted">Est. wait</p>
              <p className="font-mono text-2xl font-bold">{estWait} min</p>
            </div>
          </div>
        </div>

        <label className="mt-6 flex items-start gap-3 rounded-2xl border border-border bg-card p-4">
          <input
            type="checkbox"
            checked={priority}
            onChange={(e) => setPriority(e.target.checked)}
            className="mt-0.5 h-4 w-4 accent-[var(--brand)]"
          />
          <span>
            <span className="flex items-center gap-1.5 text-sm font-semibold text-foreground">
              <ShieldCheck className="h-4 w-4 text-brand" /> I have a verified priority registration
            </span>
            <span className="mt-1 block text-xs text-muted-foreground">
              Elderly, disability, pregnant, or caregiver of young children.
            </span>
          </span>
        </label>

        <PrimaryButton
          className="mt-6 w-full"
          onClick={() => {
            setTicket({
              code: ticketNumber,
              org,
              peopleAhead,
              avgService: org.avgService,
              priority,
            });
            navigate({ to: "/tracking" });
          }}
        >
          Join Queue
        </PrimaryButton>
      </main>
    </div>
  );
}
