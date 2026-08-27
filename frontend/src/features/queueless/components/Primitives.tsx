import type { ReactNode } from "react";
import { Link } from "@tanstack/react-router";
import { Radio, type LucideIcon } from "lucide-react";
import { cn } from "@/lib/utils";
import { STATUS, DENSITY, type StatusKey, type DensityKey } from "../data";

export function Logo({ light = false }: { light?: boolean }) {
  return (
    <Link to="/" className="inline-flex items-center gap-2">
      <span
        className={cn(
          "grid h-8 w-8 place-items-center rounded-xl",
          light ? "bg-brand text-brand-foreground" : "bg-ink text-ink-foreground",
        )}
      >
        <Radio className="h-4 w-4" />
      </span>
      <span
        className={cn(
          "font-display text-lg font-bold tracking-tight",
          light ? "text-ink-foreground" : "text-foreground",
        )}
      >
        QueueLess
      </span>
    </Link>
  );
}

export function StatusPill({ status }: { status: StatusKey }) {
  const s = STATUS[status];
  return (
    <span
      className={cn(
        "inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold",
        s.bg,
        s.text,
      )}
    >
      <span className={cn("h-1.5 w-1.5 rounded-full", s.dot)} />
      {s.label}
    </span>
  );
}

export function DensityDot({ density }: { density: DensityKey }) {
  const d = DENSITY[density];
  return (
    <span className="inline-flex items-center gap-1.5 rounded-full bg-muted px-2.5 py-1 text-xs font-medium text-muted-foreground">
      <span className={cn("h-1.5 w-1.5 rounded-full", d.dot)} />
      {d.label}
    </span>
  );
}

export function PhotoChip({
  label,
  value,
  position = "top-right",
}: {
  label: string;
  value: string;
  position?: "top-right" | "top-left" | "bottom-right" | "bottom-left";
}) {
  const pos = {
    "top-right": "top-3 right-3",
    "top-left": "top-3 left-3",
    "bottom-right": "bottom-3 right-3",
    "bottom-left": "bottom-3 left-3",
  }[position];
  return (
    <div
      className={cn(
        "absolute z-10 rounded-xl bg-card/95 px-3 py-2 shadow-float backdrop-blur",
        pos,
      )}
    >
      <p className="text-[10px] font-medium uppercase tracking-wide text-muted-foreground">
        {label}
      </p>
      <p className="font-mono text-sm font-bold text-foreground">{value}</p>
    </div>
  );
}

export function PhotoCard({
  src,
  alt,
  caption,
  chip,
  className,
}: {
  src: string;
  alt: string;
  caption?: string;
  chip?: { label: string; value: string; position?: "top-right" | "bottom-right" };
  className?: string;
}) {
  return (
    <figure className={cn("group", className)}>
      <div className="relative overflow-hidden rounded-2xl ring-1 ring-hairline">
        <img
          src={src}
          alt={alt}
          loading="lazy"
          className="aspect-[4/3] w-full object-cover transition duration-500 group-hover:scale-105"
        />
        {chip && <PhotoChip {...chip} />}
      </div>
      {caption && (
        <figcaption className="mt-2 text-xs text-ink-muted">{caption}</figcaption>
      )}
    </figure>
  );
}

export function PrimaryButton({
  children,
  onClick,
  icon: Icon,
  className,
  type = "button",
  disabled,
}: {
  children: ReactNode;
  onClick?: () => void;
  icon?: LucideIcon;
  className?: string;
  type?: "button" | "submit";
  disabled?: boolean;
}) {
  return (
    <button
      type={type}
      onClick={onClick}
      disabled={disabled}
      className={cn(
        "inline-flex items-center justify-center gap-2 rounded-xl bg-brand px-5 py-3 text-sm font-bold text-brand-foreground transition hover:bg-brand-hover disabled:cursor-not-allowed disabled:opacity-50",
        className,
      )}
    >
      {children}
      {Icon && <Icon className="h-4 w-4" />}
    </button>
  );
}

export function SecondaryButton({
  children,
  onClick,
  icon: Icon,
  className,
}: {
  children: ReactNode;
  onClick?: () => void;
  icon?: LucideIcon;
  className?: string;
}) {
  return (
    <button
      type="button"
      onClick={onClick}
      className={cn(
        "inline-flex items-center justify-center gap-2 rounded-xl border border-border bg-card px-5 py-3 text-sm font-semibold text-foreground transition hover:bg-muted",
        className,
      )}
    >
      {children}
      {Icon && <Icon className="h-4 w-4" />}
    </button>
  );
}
