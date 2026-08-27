import { createFileRoute, useNavigate } from "@tanstack/react-router";
import {
  ArrowRight,
  Bell,
  CheckCircle2,
  Clock,
  ShieldCheck,
  Sparkles,
} from "lucide-react";

import { TYPES } from "@/features/queueless/data";
import { Logo, PhotoCard, PrimaryButton } from "@/features/queueless/components/Primitives";

export const Route = createFileRoute("/")({
  head: () => ({
    meta: [
      { title: "QueueLess — Your Turn Is Coming. You Don't Have to Wait." },
      {
        name: "description",
        content:
          "Join queues at clinics, bakeries, water points and offices remotely. Track your live ticket and arrive only when it's your turn.",
      },
      { property: "og:title", content: "QueueLess — Digital Queue Management" },
      {
        property: "og:description",
        content:
          "Check availability, join remotely, and get notified before your turn — no standing in line.",
      },
    ],
  }),
  component: Landing,
});

const BENEFITS = [
  {
    icon: CheckCircle2,
    title: "Check before you go",
    body: "See if the resource is actually there before you leave home.",
  },
  { icon: Clock, title: "Wait from anywhere", body: "Hold your place in line without standing in it." },
  {
    icon: ShieldCheck,
    title: "Priority, protected",
    body: "Elderly, disabled, pregnant, and caregiver visitors get a dedicated lane.",
  },
  { icon: Bell, title: "Stay in the loop", body: "A notification finds you well before your turn arrives." },
];

const STEPS = [
  { n: "01", title: "Check availability", body: "Open a location and see its live resource and queue status." },
  { n: "02", title: "Join remotely", body: "Get a ticket number and estimated wait — no standing required." },
  { n: "03", title: "Get notified", body: "We tell you when your turn is close, so you're never late." },
];

const STATS = [
  { value: "42,000+", label: "Tickets issued" },
  { value: "18 min", label: "Avg. wait saved per visit" },
  { value: "120+", label: "Locations onboard" },
  { value: "6", label: "Service categories" },
];

const COMPARE = [
  { key: "clinic", chip: { label: "Estimated wait", value: "5 min" }, caption: "Option 1: Calm clinic check-in" },
  { key: "bakery", chip: { label: "Ready for", value: "Pickup" }, caption: "Option 2: Efficient bakery pickup" },
  { key: "water_point", chip: { label: "You are next", value: "W-119" }, caption: "Option 3: Water point turn" },
  { key: "government_office", chip: { label: "Now serving", value: "G-204" }, caption: "Option 4: Smooth office visit" },
] as const;

function Landing() {
  const navigate = useNavigate();

  return (
    <div className="min-h-screen bg-background font-sans">
      {/* Hero */}
      <section className="bg-ink text-ink-foreground">
        <div className="mx-auto max-w-6xl px-6 py-6">
          <header className="flex items-center justify-between">
            <Logo light />
            <nav className="hidden items-center gap-6 text-sm text-ink-muted md:flex">
              <a href="#how" className="transition hover:text-ink-foreground">How it works</a>
              <a href="#orgs" className="transition hover:text-ink-foreground">Organizations</a>
            </nav>
            <div className="flex items-center gap-2">
              <button
                type="button"
                onClick={() => navigate({ to: "/auth" })}
                className="rounded-xl border border-hairline px-4 py-2 text-sm font-semibold text-ink-foreground transition hover:bg-hairline"
              >
                Log in
              </button>
              <PrimaryButton onClick={() => navigate({ to: "/discovery" })} className="px-4 py-2">
                Join a Queue
              </PrimaryButton>
            </div>
          </header>

          <div className="mt-12 grid items-start gap-12 lg:grid-cols-2">
            <div>
              <span className="inline-flex items-center gap-2 rounded-full border border-hairline px-3 py-1 text-xs font-bold uppercase tracking-widest text-brand">
                <Sparkles className="h-3.5 w-3.5" />
                Digital queue management
              </span>
              <h1 className="mt-6 font-display text-5xl font-extrabold leading-[1.05] tracking-tight sm:text-6xl">
                Your Turn Is Coming.
                <br />
                You Don't Have to Wait.
              </h1>
              <p className="mt-6 max-w-lg text-lg leading-relaxed text-ink-muted">
                QueueLess turns physical lines at clinics, bakeries, water points, and offices into a
                live ticket you can track from anywhere — so you only show up when it's actually your
                turn.
              </p>
              <div className="mt-8 flex flex-wrap gap-3">
                <PrimaryButton onClick={() => navigate({ to: "/discovery" })} icon={ArrowRight}>
                  Join a Queue
                </PrimaryButton>
                <button
                  type="button"
                  onClick={() => navigate({ to: "/auth" })}
                  className="inline-flex items-center justify-center gap-2 rounded-xl border border-hairline bg-hairline px-5 py-3 text-sm font-semibold text-ink-foreground transition hover:bg-hairline/60"
                >
                  For Organizations
                </button>
              </div>
            </div>

            <div>
              <p className="text-center font-display text-xl text-ink-muted">Compare Options</p>
              <div className="mt-4 grid grid-cols-2 gap-4">
                {COMPARE.map((c) => (
                  <PhotoCard
                    key={c.key}
                    src={TYPES[c.key].image}
                    alt={c.caption}
                    caption={c.caption}
                    chip={c.chip}
                  />
                ))}
              </div>
            </div>
          </div>

          <button
            type="button"
            onClick={() => navigate({ to: "/discovery" })}
            className="mt-10 w-full rounded-2xl bg-brand px-6 py-5 text-center font-display text-lg font-bold text-brand-foreground transition hover:bg-brand-hover"
          >
            Get Started
          </button>
        </div>
      </section>

      {/* Benefits */}
      <section className="mx-auto max-w-6xl px-6 py-20">
        <h2 className="max-w-2xl font-display text-3xl font-bold tracking-tight text-foreground">
          Built around real waiting, not appointments
        </h2>
        <div className="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
          {BENEFITS.map((b) => (
            <div key={b.title} className="rounded-2xl border border-border bg-card p-6 shadow-soft">
              <b.icon className="h-6 w-6 text-brand" />
              <h3 className="mt-4 font-display text-base font-bold text-foreground">{b.title}</h3>
              <p className="mt-2 text-sm leading-relaxed text-muted-foreground">{b.body}</p>
            </div>
          ))}
        </div>
      </section>

      {/* How it works */}
      <section id="how" className="bg-muted py-20">
        <div className="mx-auto max-w-6xl px-6">
          <h2 className="font-display text-3xl font-bold tracking-tight text-foreground">How it works</h2>
          <div className="mt-10 grid gap-6 md:grid-cols-3">
            {STEPS.map((s) => (
              <div key={s.n} className="rounded-2xl bg-card p-6 shadow-soft">
                <span className="font-mono text-sm font-bold text-brand">{s.n}</span>
                <h3 className="mt-3 font-display text-lg font-bold text-foreground">{s.title}</h3>
                <p className="mt-2 text-sm leading-relaxed text-muted-foreground">{s.body}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Supported organizations */}
      <section id="orgs" className="mx-auto max-w-6xl px-6 py-20">
        <h2 className="font-display text-3xl font-bold tracking-tight text-foreground">
          Where QueueLess is used
        </h2>
        <div className="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          {Object.entries(TYPES).map(([key, t]) => (
            <div key={key} className="relative overflow-hidden rounded-2xl ring-1 ring-border">
              <img src={t.image} alt={t.label} loading="lazy" className="aspect-[16/9] w-full object-cover" />
              <span className="absolute bottom-3 left-3 inline-flex items-center gap-2 rounded-full bg-card/95 px-3 py-1.5 text-sm font-semibold text-foreground shadow-float backdrop-blur">
                <t.icon className="h-4 w-4" />
                {t.label}
              </span>
            </div>
          ))}
        </div>
      </section>

      {/* Stats */}
      <section className="bg-ink py-16 text-ink-foreground">
        <div className="mx-auto grid max-w-6xl grid-cols-2 gap-8 px-6 lg:grid-cols-4">
          {STATS.map((s) => (
            <div key={s.label}>
              <p className="font-mono text-3xl font-bold text-brand">{s.value}</p>
              <p className="mt-1 text-sm text-ink-muted">{s.label}</p>
            </div>
          ))}
        </div>
      </section>

      <footer className="mx-auto flex max-w-6xl flex-col gap-3 px-6 py-10 text-sm text-muted-foreground sm:flex-row sm:items-center sm:justify-between">
        <Logo />
        <p>Digital queues for the services people can't afford to wait blindly for.</p>
      </footer>
    </div>
  );
}
