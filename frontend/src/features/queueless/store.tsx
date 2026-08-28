import {
  createContext,
  useCallback,
  useContext,
  useEffect,
  useMemo,
  useRef,
  useState,
  type ReactNode,
} from "react";
import { toast } from "sonner";
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
  /** Live number of people ahead for the active ticket. */
  peopleAhead: number;
  initialAhead: number;
  paused: boolean;
  setPaused: (fn: (p: boolean) => boolean) => void;
  joinQueue: (ticket: Ticket) => void;
  leaveQueue: () => void;
};

const QueueContext = createContext<QueueState | null>(null);

export function QueueProvider({ children }: { children: ReactNode }) {
  const [selectedOrg, setSelectedOrg] = useState<Org | null>(null);
  const [ticket, setTicket] = useState<Ticket | null>(null);
  const [peopleAhead, setPeopleAhead] = useState(0);
  const [initialAhead, setInitialAhead] = useState(0);
  const [paused, setPausedState] = useState(false);
  const notified = useRef({ soon: false, turn: false });

  const joinQueue = useCallback((next: Ticket) => {
    notified.current = { soon: false, turn: false };
    setTicket(next);
    setPeopleAhead(next.peopleAhead);
    setInitialAhead(Math.max(1, next.peopleAhead));
    setPausedState(false);
    toast.success(`You joined the queue at ${next.org.name}`, {
      description: `Ticket ${next.code} — we'll alert you when your turn is close.`,
    });
  }, []);

  const leaveQueue = useCallback(() => {
    setTicket(null);
    setPeopleAhead(0);
    setInitialAhead(0);
    setPausedState(false);
    notified.current = { soon: false, turn: false };
  }, []);

  // Global queue progression — keeps running while browsing other pages.
  useEffect(() => {
    if (!ticket || paused) return;
    const id = setInterval(() => {
      setPeopleAhead((n) => Math.max(0, n - 1));
    }, 3600);
    return () => clearInterval(id);
  }, [ticket, paused]);

  // Turn-is-close notifications.
  useEffect(() => {
    if (!ticket) return;
    if (peopleAhead === 0 && !notified.current.turn) {
      notified.current.turn = true;
      toast.success("It's your turn!", {
        description: `Ticket ${ticket.code} — head to the counter at ${ticket.org.name}.`,
        duration: 10000,
      });
      return;
    }
    if (peopleAhead > 0 && peopleAhead <= 2 && !notified.current.soon) {
      notified.current.soon = true;
      toast(`Your turn is coming up at ${ticket.org.name}`, {
        description: `Only ${peopleAhead} ${peopleAhead === 1 ? "person" : "people"} ahead — about ${peopleAhead * ticket.avgService} min. Start heading over.`,
        duration: 10000,
      });
    }
  }, [peopleAhead, ticket]);

  const value = useMemo(
    () => ({
      selectedOrg,
      setSelectedOrg,
      ticket,
      peopleAhead,
      initialAhead,
      paused,
      setPaused: (fn: (p: boolean) => boolean) => setPausedState((p) => fn(p)),
      joinQueue,
      leaveQueue,
    }),
    [selectedOrg, ticket, peopleAhead, initialAhead, paused, joinQueue, leaveQueue],
  );

  return <QueueContext.Provider value={value}>{children}</QueueContext.Provider>;
}

export function useQueue() {
  const ctx = useContext(QueueContext);
  if (!ctx) throw new Error("useQueue must be used inside QueueProvider");
  return ctx;
}
