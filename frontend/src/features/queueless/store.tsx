import { createContext, useContext, useMemo, useState, type ReactNode } from "react";
import type { Org } from "./data";

export type Ticket = {
  code: string;
  org: Org;
  peopleAhead: number;
  avgService: number;
};

type QueueState = {
  selectedOrg: Org | null;
  setSelectedOrg: (org: Org | null) => void;
  ticket: Ticket | null;
  setTicket: (ticket: Ticket | null) => void;
};

const QueueContext = createContext<QueueState | null>(null);

export function QueueProvider({ children }: { children: ReactNode }) {
  const [selectedOrg, setSelectedOrg] = useState<Org | null>(null);
  const [ticket, setTicket] = useState<Ticket | null>(null);

  const value = useMemo(
    () => ({ selectedOrg, setSelectedOrg, ticket, setTicket }),
    [selectedOrg, ticket],
  );

  return <QueueContext.Provider value={value}>{children}</QueueContext.Provider>;
}

export function useQueue() {
  const ctx = useContext(QueueContext);
  if (!ctx) throw new Error("useQueue must be used inside QueueProvider");
  return ctx;
}
