import { useState } from "react";
import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { ArrowLeft, Lock, Mail, User as UserIcon } from "lucide-react";

import { Logo, PrimaryButton } from "@/features/queueless/components/Primitives";

export const Route = createFileRoute("/auth")({
  head: () => ({
    meta: [
      { title: "Log in or sign up — QueueLess" },
      { name: "description", content: "Access your QueueLess account to track queues and manage your organization." },
      { property: "og:title", content: "Log in — QueueLess" },
      { property: "og:description", content: "Join once, queue anywhere QueueLess is used." },
    ],
  }),
  component: AuthPage,
});

function AuthPage() {
  const navigate = useNavigate();
  const [mode, setMode] = useState<"login" | "signup">("login");

  return (
    <div className="grid min-h-screen bg-background font-sans lg:grid-cols-2">
      <aside className="hidden flex-col justify-between bg-ink p-12 text-ink-foreground lg:flex">
        <Logo light />
        <div>
          <p className="font-display text-2xl font-semibold leading-snug">
            "I checked from home, joined the queue, and only walked over when my number was close."
          </p>
          <p className="mt-4 text-sm text-ink-muted">
            — A resident using QueueLess at a community bakery
          </p>
        </div>
        <p className="text-xs text-ink-muted">© QueueLess</p>
      </aside>

      <main className="flex items-center justify-center px-6 py-12">
        <div className="w-full max-w-sm">
          <button
            type="button"
            onClick={() => navigate({ to: "/" })}
            className="mb-8 flex items-center gap-1.5 text-sm text-muted-foreground transition hover:text-foreground"
          >
            <ArrowLeft className="h-4 w-4" /> Back
          </button>

          <div className="flex rounded-xl bg-muted p-1">
            {(["login", "signup"] as const).map((m) => (
              <button
                key={m}
                type="button"
                onClick={() => setMode(m)}
                className={`flex-1 rounded-lg py-2 text-sm font-semibold transition ${
                  mode === m ? "bg-card text-foreground shadow-soft" : "text-muted-foreground"
                }`}
              >
                {m === "login" ? "Log in" : "Sign up"}
              </button>
            ))}
          </div>

          <h1 className="mt-8 font-display text-2xl font-bold text-foreground">
            {mode === "login" ? "Welcome back" : "Create your account"}
          </h1>
          <p className="mt-1 text-sm text-muted-foreground">
            {mode === "login"
              ? "Log in to track your queues."
              : "Join once, queue anywhere QueueLess is used."}
          </p>

          <form
            className="mt-8 space-y-4"
            onSubmit={(e) => {
              e.preventDefault();
              navigate({ to: "/discovery" });
            }}
          >
            {mode === "signup" && (
              <Field label="Full name" icon={UserIcon} type="text" placeholder="Your name" />
            )}
            <Field label="Email" icon={Mail} type="email" placeholder="you@example.com" />
            <Field label="Password" icon={Lock} type="password" placeholder="••••••••" />

            <PrimaryButton type="submit" className="w-full">
              {mode === "login" ? "Log in" : "Create account"}
            </PrimaryButton>
          </form>
        </div>
      </main>
    </div>
  );
}

function Field({
  label,
  icon: Icon,
  type,
  placeholder,
}: {
  label: string;
  icon: React.ElementType;
  type: string;
  placeholder: string;
}) {
  return (
    <label className="block">
      <span className="text-xs font-semibold text-muted-foreground">{label}</span>
      <span className="mt-1.5 flex items-center gap-2 rounded-xl border border-border bg-card px-3 py-2.5 focus-within:border-brand">
        <Icon className="h-4 w-4 text-muted-foreground" />
        <input
          type={type}
          placeholder={placeholder}
          className="w-full bg-transparent text-sm text-foreground outline-none placeholder:text-muted-foreground"
        />
      </span>
    </label>
  );
}
