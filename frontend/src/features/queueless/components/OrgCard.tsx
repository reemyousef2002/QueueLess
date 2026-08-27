import { TYPES, type Org } from "../data";
import { DensityDot, PrimaryButton, StatusPill } from "./Primitives";

export function OrgCard({ org, onJoin }: { org: Org; onJoin: (org: Org) => void }) {
  const type = TYPES[org.type];
  const Icon = type.icon;
  const canJoin = org.open && org.status !== "depleted";
  const estWait = org.waitMins * org.queueSize;

  return (
    <article className="flex flex-col overflow-hidden rounded-2xl border border-border bg-card shadow-soft">
      <div className="relative">
        <img
          src={type.image}
          alt={`${type.label} — ${org.name}`}
          loading="lazy"
          className="aspect-[16/10] w-full object-cover"
        />
        <span className="absolute left-3 top-3 inline-flex items-center gap-1.5 rounded-full bg-card/95 px-2.5 py-1 text-xs font-semibold text-foreground shadow-float backdrop-blur">
          <Icon className="h-3.5 w-3.5" />
          {type.label}
        </span>
      </div>

      <div className="flex flex-1 flex-col gap-4 p-5">
        <div className="flex items-start justify-between gap-3">
          <h3 className="font-display text-base font-bold text-foreground">{org.name}</h3>
          <span className="shrink-0 text-xs font-medium text-muted-foreground">
            {org.open ? "Open" : "Closed"}
          </span>
        </div>

        <div className="flex flex-wrap gap-2">
          <StatusPill status={org.status} />
          <DensityDot density={org.density} />
        </div>

        <div className="grid grid-cols-3 gap-2 rounded-xl bg-muted p-3 text-center">
          <div>
            <p className="font-mono text-base font-bold text-foreground">{org.queueSize}</p>
            <p className="text-[11px] text-muted-foreground">in queue</p>
          </div>
          <div>
            <p className="font-mono text-base font-bold text-foreground">~{estWait}m</p>
            <p className="text-[11px] text-muted-foreground">est. wait</p>
          </div>
          <div>
            <p className="font-mono text-base font-bold text-foreground">{org.counters}</p>
            <p className="text-[11px] text-muted-foreground">counters</p>
          </div>
        </div>

        <PrimaryButton
          onClick={() => canJoin && onJoin(org)}
          disabled={!canJoin}
          className="mt-auto w-full"
        >
          {!org.open ? "Closed" : org.status === "depleted" ? "Resource depleted" : "Join Queue"}
        </PrimaryButton>
      </div>
    </article>
  );
}
