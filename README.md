# Your Turn Now

import React, { useState, useEffect, useMemo } from "react";
import {
  Stethoscope, Wheat, Droplets, UtensilsCrossed, GraduationCap, Landmark,
  Clock, Users, ArrowRight, ArrowLeft, ShieldCheck, Pause, Play, LogOut,
  Mail, Lock, User as UserIcon, CheckCircle2, Circle, Bell, Radio, Sparkles
} from "lucide-react";

/* ------------------------------------------------------------------ */
/* Design tokens                                                       */
/* ------------------------------------------------------------------ */
const FONTS = {
  display: "'Sora', sans-serif",
  mono: "'IBM Plex Mono', monospace",
  body: "'Inter', sans-serif",
};

/* Picsum Photos — reliable placeholder host, no hotlink/CORS restrictions.
   Swap these for your own real photos later; seeds just keep each category consistent. */
const IMG = {
  hero: "https://picsum.photos/seed/queueless-hero/900/1100",
  clinic: "https://picsum.photos/seed/queueless-clinic/800/800",
  bakery: "https://picsum.photos/seed/queueless-bakery/800/800",
  water_point: "https://picsum.photos/seed/queueless-water/800/800",
  community_kitchen: "https://picsum.photos/seed/queueless-kitchen/800/800",
  university_office: "https://picsum.photos/seed/queueless-university/800/800",
  government_office: "https://picsum.photos/seed/queueless-government/800/800",
};

const TYPES = {
  clinic: { label: "Clinic", icon: Stethoscope },
  bakery: { label: "Bakery", icon: Wheat },
  water_point: { label: "Water Point", icon: Droplets },
  community_kitchen: { label: "Community Kitchen", icon: UtensilsCrossed },
  university_office: { label: "University Office", icon: GraduationCap },
  government_office: { label: "Government Office", icon: Landmark },
};

const STATUS = {
  available: { label: "Available", ring: "ring-emerald-200", text: "text-emerald-700", bg: "bg-emerald-50", dot: "bg-emerald-500" },
  limited: { label: "Limited", ring: "ring-amber-200", text: "text-amber-700", bg: "bg-amber-50", dot: "bg-amber-500" },
  depleted: { label: "Depleted", ring: "ring-rose-200", text: "text-rose-700", bg: "bg-rose-50", dot: "bg-rose-500" },
};

const DENSITY = {
  green: { label: "Calm", dot: "bg-emerald-500" },
  yellow: { label: "Busy", dot: "bg-amber-500" },
  red: { label: "Crowded", dot: "bg-rose-500" },
};

const ORGS = [
  { id: 1, name: "Al-Amal Clinic", type: "clinic", status: "available", density: "green", queueSize: 6, waitMins: 3, avgService: 4, open: true, counters: 2, prefix: "A" },
  { id: 2, name: "Barakah Community Bakery", type: "bakery", status: "limited", density: "yellow", queueSize: 14, waitMins: 2, avgService: 2, open: true, counters: 1, prefix: "B" },
  { id: 3, name: "Al-Nour Water Point", type: "water_point", status: "available", density: "yellow", queueSize: 9, waitMins: 3, avgService: 3, open: true, counters: 3, prefix: "W" },
  { id: 4, name: "Rahma Community Kitchen", type: "community_kitchen", status: "depleted", density: "red", queueSize: 21, waitMins: 4, avgService: 4, open: true, counters: 2, prefix: "K" },
  { id: 5, name: "Student Affairs Office", type: "university_office", status: "available", density: "green", queueSize: 3, waitMins: 5, avgService: 5, open: true, counters: 1, prefix: "S" },
  { id: 6, name: "Civil Registry Office", type: "government_office", status: "limited", density: "yellow", queueSize: 11, waitMins: 3, avgService: 3, open: false, counters: 2, prefix: "G" },
];

/* ------------------------------------------------------------------ */
/* Small shared building blocks                                        */
/* ------------------------------------------------------------------ */
function Logo({ size = 22, light = false }) {
  return (
    


      


        
      


      
        QueueLess
      
    


  );
}

function StatusPill({ status }) {
  const s = STATUS[status];
  return (
    
      
      {s.label}
    
  );
}

function DensityDot({ density }) {
  const d = DENSITY[density];
  return (
    
      
      {d.label}
    
  );
}

/* Floating status chip that sits on top of a photo — the signature element,
   matching the "You are next: A-119" / "Estimated Wait: 5 min" reference style */
function PhotoChip({ icon: Icon, label, value, position = "top-right" }) {
  const pos = {
    "top-right": "top-3 right-3",
    "top-left": "top-3 left-3",
    "bottom-right": "bottom-3 right-3",
    "bottom-left": "bottom-3 left-3",
  }[position];
  return (
    


      {Icon && (
        


          
        


      )}
      


        

{label}


        


          {value}
        


      


    


  );
}

function PhotoCard({ src, alt, chip, className = "", children }) {
  return (
    


      
      {chip && }
      {children}
    


  );
}

function PrimaryButton({ children, onClick, icon: Icon, className = "", type = "button", disabled }) {
  return (
    
      {children}
      {Icon && }
    
  );
}

function SecondaryButton({ children, onClick, icon: Icon, className = "" }) {
  return (
    
      {children}
      {Icon && }
    
  );
}

function ProtoNav({ page, setPage }) {
  const steps = [
    ["landing", "Landing"],
    ["auth", "Login"],
    ["discovery", "Discovery"],
    ["join", "Join Queue"],
    ["tracking", "Live Tracking"],
  ];
  return (
    


      


        
          Prototype
        
        {steps.map(([id, label]) => (
           setPage(id)}
            className={`shrink-0 rounded-full px-3 py-1 transition ${page === id ? "bg-amber-500 text-slate-900" : "hover:bg-slate-800"}`}
          >
            {label}
          
        ))}
      


    


  );
}

/* ------------------------------------------------------------------ */
/* Landing page                                                        */
/* ------------------------------------------------------------------ */
function Landing({ setPage }) {
  const benefits = [
    { icon: CheckCircle2, title: "Check before you go", body: "See if the resource is actually there before you leave home." },
    { icon: Clock, title: "Wait from anywhere", body: "Hold your place in line without standing in it." },
    { icon: ShieldCheck, title: "Priority, protected", body: "Elderly, disabled, pregnant, and caregiver visitors get a dedicated lane." },
    { icon: Bell, title: "Stay in the loop", body: "A notification finds you well before your turn arrives." },
  ];
  const steps = [
    { n: "01", title: "Check availability", body: "Open a location and see its live resource and queue status." },
    { n: "02", title: "Join remotely", body: "Get a ticket number and estimated wait — no standing required." },
    { n: "03", title: "Get notified", body: "We tell you when your turn is close, so you're never late." },
  ];
  const orgTypes = Object.entries(TYPES);
  const stats = [
    { value: "42,000+", label: "Tickets issued" },
    { value: "18 min", label: "Avg. wait saved per visit" },
    { value: "120+", label: "Locations onboard" },
    { value: "6", label: "Service categories" },
  ];

  return (
    


      


        


          
          
            How it works
            Organizations
          
          


             setPage("auth")} className="!px-4 !py-2">Log in
             setPage("discovery")} className="!px-4 !py-2">Join a Queue
          


        


      



      {/* Hero — text + buttons on the left, "Compare Options" photo grid on the right,
          one full-width "Get Started" bar underneath both, matching the reference layout */}
      


        


          


            


              
                 Digital queue management
              
              


                Your Turn Is Coming.
                

                You Don't Have to Wait.
              


              


                QueueLess turns physical lines at clinics, bakeries, water points, and offices into a
                live ticket you can track from anywhere — so you only show up when it's actually your turn.
              


              


                 setPage("discovery")} icon={ArrowRight}>Join a Queue
                 setPage("auth")}
                  className="inline-flex items-center justify-center gap-2 rounded-xl border border-white/30 bg-white/5 px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/15"
                >
                  For Organizations
                
              


            



            


              

Compare Options


              


                
                
                
                
              


            


          



           setPage("discovery")}
            className="mt-8 w-full rounded-2xl bg-amber-500 px-6 py-5 text-center text-lg font-bold text-slate-900 transition hover:bg-amber-400"
          >
            Get Started
          
        


      



      {/* Benefits */}
      


        


          


            Built around real waiting, not appointments
          


          


            {benefits.map((b) => (
              


                
                

{b.title}


                

{b.body}


              


            ))}
          


        


      



      {/* How it works */}
      


        


          How it works
        


        


          {steps.map((s, i) => (
            


              
                {s.n}
              
              

{s.title}


              

{s.body}


              {i < steps.length - 1 && }
            


          ))}
        


      



      {/* Supported organizations */}
      


        


          Where QueueLess is used
        


        


          {orgTypes.map(([key, t]) => (
            


              
              


              


                
                {t.label}
              


            


          ))}
        


      



      {/* Stats */}
      


        


          


            {stats.map((s) => (
              


                

{s.value}


                

{s.label}


              


            ))}
          


        


      



      


        


          
          Digital queues for the services people can't afford to wait blindly for.
        


      


    


  );
}

/* ------------------------------------------------------------------ */
/* Auth page                                                            */
/* ------------------------------------------------------------------ */
function Auth({ setPage }) {
  const [mode, setMode] = useState("login");
  return (
    


      


        
        


        


        


          


            "I checked from home, joined the queue, and only walked over when my number was close."
          


          

— A resident using QueueLess at a community bakery


        


        

© QueueLess


      



      


        


           setPage("landing")} className="flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-700 mb-8">
             Back
          

          


            {["login", "signup"].map((m) => (
               setMode(m)}
                className={`flex-1 rounded-lg py-2 text-sm font-semibold transition ${mode === m ? "bg-white shadow-sm text-slate-900" : "text-slate-500"}`}
              >
                {m === "login" ? "Log in" : "Sign up"}
              
            ))}
          



          


            {mode === "login" ? "Welcome back" : "Create your account"}
          


          


            {mode === "login" ? "Log in to track your queues." : "Join once, queue anywhere QueueLess is used."}
          



          

 { e.preventDefault(); setPage("discovery"); }}>
            {mode === "signup" && (
              


                Full name
                


                  
                  
                


              


            )}
            


              Email
              


                
                @example.com" />
              
            
            


              Password
              


                
                
              


            


            
              {mode === "login" ? "Log in" : "Create account"}
            
          
        
      
    
  );
}

/* ------------------------------------------------------------------ */
/* Service discovery page                                              */
/* ------------------------------------------------------------------ */
function OrgCard({ org, onJoin }) {
  const type = TYPES[org.type];
  const canJoin = org.open && org.status !== "depleted";
  const estWait = org.waitMins * org.queueSize;
  return (
    


      
        


        


          
          {type.label}
        


      
      


        


          

{org.name}


          {org.open ? "Open" : "Closed"}
        



        


          
          
        



        


          


            

{org.queueSize}


            

in queue


          


          


            

~{estWait}m


            

est. wait


          


          


            

{org.counters}


            

counters


          


        


      


      


         canJoin && onJoin(org)} disabled={!canJoin} className="w-full">
          {!org.open ? "Closed" : org.status === "depleted" ? "Resource depleted" : "Join Queue"}
        
      


    


  );
}

function Discovery({ setPage, setSelectedOrg }) {
  const [filter, setFilter] = useState("all");
  const filtered = useMemo(() => (filter === "all" ? ORGS : ORGS.filter((o) => o.type === filter)), [filter]);
  const chips = [{ id: "all", label: "All" }, ...Object.entries(TYPES).map(([id, t]) => ({ id, label: t.label }))];

  return (
    


      


        


          
           setPage("landing")} className="text-sm text-slate-500 hover:text-slate-700">Log out
        


      



      


        

Find a service near you


        

Check resource availability and crowd levels before you join a queue.



        


          {chips.map((c) => (
             setFilter(c.id)}
              className={`rounded-full px-4 py-1.5 text-sm font-medium transition ${filter === c.id ? "bg-indigo-900 text-white" : "bg-white ring-1 ring-slate-200 text-slate-600 hover:bg-slate-100"}`}
            >
              {c.label}
            
          ))}
        



        


          {filtered.map((org) => (
             { setSelectedOrg(o); setPage("join"); }} />
          ))}
        


      


    


  );
}

/* ------------------------------------------------------------------ */
/* Join queue page                                                     */
/* ------------------------------------------------------------------ */
function Join({ setPage, org, setTicket }) {
  const [priority, setPriority] = useState(false);
  if (!org) {
    return (
      


         setPage("discovery")}>Choose a service first
      


    );
  }
  const type = TYPES[org.type];
  const peopleAhead = priority ? Math.max(1, Math.round(org.queueSize / 3)) : org.queueSize;
  const ticketNumber = `${org.prefix}-${100 + org.queueSize + 4}`;
  const estWait = peopleAhead * org.avgService;

  return (
    


      


        


          
           setPage("discovery")} className="flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-700">
             Back to services
          
        


      



      


        
          


          


            

{org.name}


            {type.label}
          


        

        


          


            Your ticket
            
          


          

{ticketNumber}


          


            


              

People ahead


              

{peopleAhead}


            


            


              

Est. wait


              

{estWait} min


            


          


        



        
           setPriority(e.target.checked)} className="mt-0.5 h-4 w-4 accent-indigo-800" />
          
            
               I have a verified priority registration
            
            Elderly, disability, pregnant, or caregiver of young children.
          
        

         {
            setTicket({ code: ticketNumber, org, peopleAhead, avgService: org.avgService, priority });
            setPage("tracking");
          }}
        >
          Join Queue
        
      


    


  );
}

/* ------------------------------------------------------------------ */
/* Live tracking page — the main feature                               */
/* ------------------------------------------------------------------ */
function Tracking({ setPage, ticket }) {
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
      


         setPage("discovery")}>Join a queue first
      


    );
  }

  const type = TYPES[ticket.org.type];
  const estWait = peopleAhead * ticket.avgService;
  const yourTurn = peopleAhead === 0;
  const maxDots = 10;
  const visibleDots = Math.min(peopleAhead, maxDots);
  const overflow = peopleAhead - visibleDots;

  return (
    


      


        


          
           setPage("discovery")} className="flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-700">
             Services
          
        


      



      


        
          


          


            
            {ticket.org.name}
            {ticket.priority && (
              
                 Priority
              
            )}
          


        

        


          
            {yourTurn ? "It's your turn" : "Your ticket"}
          
          

{ticket.code}


          


            
            {yourTurn ? "Please head to the counter" : paused ? "Paused — your place is held" : "Waiting"}
          


        



        


          


            People ahead of you
            Updated {secondsAgo}s ago
          


          


            {Array.from({ length: visibleDots }).map((_, i) => (
              
            ))}
            {Array.from({ length: Math.max(0, initialAhead - visibleDots - overflow) }).map((_, i) => (
              
            ))}
            {overflow > 0 && +{overflow} more}
          


        



        


          


            
            

{peopleAhead}


            

ahead


          


          


            
            

{estWait}m


            

est. wait


          


          


            
            

{ticket.avgService}m


            

avg. service


          


        



        


           setPaused((p) => !p)}>
            {paused ? "Resume Queue" : "Pause Queue"}
          
           setPage("discovery")}>
            Leave Queue
          
        


      


    


  );
}

/* ------------------------------------------------------------------ */
/* Root app                                                             */
/* ------------------------------------------------------------------ */
export default function QueueLessApp() {
  const [page, setPage] = useState("landing");
  const [selectedOrg, setSelectedOrg] = useState(null);
  const [ticket, setTicket] = useState(null);

  return (
    


      @import url('https://fonts.googleapis.com/css2?family=Sora:wght@500;600;700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500;600;700&display=swap');
      `}</style>
      <ProtoNav page={page} setPage={setPage} />
      {page === "landing" && <Landing setPage={setPage} />}
      {page === "auth" && <Auth setPage={setPage} />}
      {page === "discovery" && <Discovery setPage={setPage} setSelectedOrg={setSelectedOrg} />}
      {page === "join" && <Join setPage={setPage} org={selectedOrg} setTicket={setTicket} />}
      {page === "tracking" && <Tracking setPage={setPage} ticket={ticket} />}
    </div>
  );
}


ok for the landing page i want it to look something like the photo i sent and for the photos that in the discovery list why i cant see any photos and make it as folder of the frontend not like jsx

This project was built with [Lovable](https://lovable.dev).

## Build with Lovable

Continue developing this project in the [Lovable editor](https://lovable.dev/projects/d6429a7a-b670-442d-87ff-41ea2e7a6979).

- **Ship faster**: describe what you want to build and Lovable handles the code.
- **Stay in sync**: every change made in Lovable is committed straight to this repository.
- **Full ownership**: this code is yours. Push to `main` on GitHub and your changes sync back into Lovable, ready for your next prompt.

## Development

Prefer working locally? You need Node.js and npm — [install with nvm](https://github.com/nvm-sh/nvm#installing-and-updating).

```sh
git clone <this-repository-url>
cd <repository-name>
npm i
npm run dev
```
