import { useEffect, useState } from "react";
import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { ArrowLeft, Clock, LogOut, Pause, Play, Users } from "lucide-react";

import { TYPES } from "@/features/queueless/data";
import { Logo, PrimaryButton, SecondaryButton } from "@/features/queueless/components/Primitives";
import { useQueue } from "@/features/queueless/store";

export const Route = createFileRoute("/tracking")({
  head: () => ({
    meta: [
      { title: "Live queue tracking — QueueLess" },
      { name: "description", content: "Watch your place in line update live and head over only when your turn is close." },
      { property: "og:title", content: "Live tracking — QueueLess" },
      { property: "og:description", content: "Your ticket, people ahead and estimated wait, updating in real time." },
    ],
  }),
  component: Tracking,
});

function Tracking() {
  const navigate = useNavigate();
  const { ticket } = useQueue();
  const [peopleAhead, setPeopleAhead] = useState(ticket?.peopleAhead ?? 0);
  const [initialAhead] = useState(ticket?.peopleAhead ?? 1);
  const [paused, setPaused] = useState(false);
  const [secondsAgo, setSecondsAgo] = useState(0);

  useEffect(() => {
    if (!ticket) return;
    const tick = setInterval(() => {
      if (!paused) {
        setPeopleAhead((n) => Math.max(0, n - 1));
        setSecondsAgo(0);
      }
    }, 3600);
    return () => clearInterval(tick);
  }, [paused, ticket]);

  useEffect(() => {
    const t = setInterval(() => setSecondsAgo((s) => s + 1), 1000);
    return () => clearInterval(t);
  }, []);

  if (!ticket) {
    return (
      <div className="flex min-h-screen items-center justify-center bg-background font-sans">
        <PrimaryButton onClick={() => navigate({ to: "/discovery" })}>Join a queue first</PrimaryButton>
      </div>
    );
  }

  const type = TYPES[ticket.org.type];
  const estWait = peopleAhead * ticket.avgService;
  const yourTurn = peopleAhead === 0;
  const visibleDots = Math.min(peopleAhead, 10);
  const overflow = peopleAhead - visibleDots;

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
            <ArrowLeft className="h-4 w-4" /> Services
          </button>
        </div>
      </header>

      <main className="mx-auto max-w-2xl px-6 py-10">
        <div className="overflow-hidden rounded-2xl border border-border bg-card shadow-soft">
          <img src={type.image} alt={ticket.org.name} loading="lazy" className="aspect-[16/6] w-full object-cover" />
          <div className="flex flex-wrap items-center gap-2 p-5">
            <type.icon className="h-4 w-4 text-muted-foreground" />
            <span className="font-display text-base font-bold text-foreground">{ticket.org.name}</span>
          </div>
        </div>

        <div className="mt-6 rounded-2xl bg-ink p-8 text-center text-ink-foreground">
          <p className="text-xs font-semibold uppercase tracking-widest text-brand">
            {yourTurn ? "It's your turn" : "Your ticket"}
          </p>
          <p className="mt-2 font-mono text-6xl font-bold">{ticket.code}</p>
          <p className="mt-3 inline-flex items-center gap-2 text-sm text-ink-muted">
            <span className={`h-2 w-2 rounded-full ${yourTurn ? "bg-success" : paused ? "bg-warning" : "bg-brand"}`} />
            {yourTurn
              ? "Please head to the counter"
              : paused
                ? "Paused — your place is held"
                : "Waiting"}
          </p>
        </div>

        <div className="mt-6 rounded-2xl border border-border bg-card p-5 shadow-soft">
          <div className="flex items-center justify-between text-sm">
            <span className="font-semibold text-foreground">People ahead of you</span>
            <span className="text-xs text-muted-foreground">Updated {secondsAgo}s ago</span>
          </div>
          <div className="mt-4 flex flex-wrap items-center gap-2">
            {Array.from({ length: visibleDots }).map((_, i) => (
              <span key={`ahead-${i}`} className="h-3 w-3 rounded-full bg-ink" />
            ))}
            {Array.from({ length: Math.max(0, initialAhead - visibleDots - overflow) }).map((_, i) => (
              <span key={`done-${i}`} className="h-3 w-3 rounded-full bg-muted" />
            ))}
            {overflow > 0 && (
              <span className="text-xs font-medium text-muted-foreground">+{overflow} more</span>
            )}
          </div>
        </div>

        <div className="mt-6 grid grid-cols-3 gap-4">
          <Metric icon={Users} value={String(peopleAhead)} label="ahead" />
          <Metric icon={Clock} value={`${estWait}m`} label="est. wait" />
          <Metric icon={Clock} value={`${ticket.avgService}m`} label="avg. service" />
        </div>

        <div className="mt-6 flex flex-wrap gap-3">
          <PrimaryButton
            className="flex-1"
            icon={paused ? Play : Pause}
            onClick={() => setPaused((p) => !p)}
          >
            {paused ? "Resume Queue" : "Pause Queue"}
          </PrimaryButton>
          <SecondaryButton className="flex-1" icon={LogOut} onClick={() => navigate({ to: "/discovery" })}>
            Leave Queue
          </SecondaryButton>
        </div>
      </main>
    </div>
  );
}

function Metric({
  icon: Icon,
  value,
  label,
}: {
  icon: React.ElementType;
  value: string;
  label: string;
}) {
  return (
    <div className="rounded-2xl border border-border bg-card p-4 text-center shadow-soft">
      <Icon className="mx-auto h-4 w-4 text-muted-foreground" />
      <p className="mt-2 font-mono text-xl font-bold text-foreground">{value}</p>
      <p className="text-[11px] text-muted-foreground">{label}</p>
    </div>
  );
}
