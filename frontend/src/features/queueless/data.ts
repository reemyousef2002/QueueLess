import {
  Stethoscope,
  Wheat,
  Droplets,
  UtensilsCrossed,
  GraduationCap,
  Landmark,
  type LucideIcon,
} from "lucide-react";

import clinicImg from "@/assets/clinic.jpg";
import bakeryImg from "@/assets/bakery.jpg";
import waterImg from "@/assets/water.jpg";
import kitchenImg from "@/assets/kitchen.jpg";
import universityImg from "@/assets/university.jpg";
import governmentImg from "@/assets/government.jpg";

export type OrgType =
  | "clinic"
  | "bakery"
  | "water_point"
  | "community_kitchen"
  | "university_office"
  | "government_office";

export type StatusKey = "available" | "limited" | "depleted";
export type DensityKey = "green" | "yellow" | "red";

export const TYPES: Record<OrgType, { label: string; icon: LucideIcon; image: string }> = {
  clinic: { label: "Clinic", icon: Stethoscope, image: clinicImg },
  bakery: { label: "Bakery", icon: Wheat, image: bakeryImg },
  water_point: { label: "Water Point", icon: Droplets, image: waterImg },
  community_kitchen: { label: "Community Kitchen", icon: UtensilsCrossed, image: kitchenImg },
  university_office: { label: "University Office", icon: GraduationCap, image: universityImg },
  government_office: { label: "Government Office", icon: Landmark, image: governmentImg },
};

export const STATUS: Record<StatusKey, { label: string; text: string; bg: string; dot: string }> = {
  available: {
    label: "Available",
    text: "text-success",
    bg: "bg-success-soft",
    dot: "bg-success",
  },
  limited: { label: "Limited", text: "text-warning", bg: "bg-warning-soft", dot: "bg-warning" },
  depleted: { label: "Depleted", text: "text-danger", bg: "bg-danger-soft", dot: "bg-danger" },
};

export const DENSITY: Record<DensityKey, { label: string; dot: string }> = {
  green: { label: "Calm", dot: "bg-success" },
  yellow: { label: "Busy", dot: "bg-warning" },
  red: { label: "Crowded", dot: "bg-danger" },
};

export type Org = {
  id: number;
  name: string;
  type: OrgType;
  status: StatusKey;
  density: DensityKey;
  queueSize: number;
  waitMins: number;
  avgService: number;
  open: boolean;
  counters: number;
  prefix: string;
};

export const ORGS: Org[] = [
  { id: 1, name: "Al-Amal Clinic", type: "clinic", status: "available", density: "green", queueSize: 6, waitMins: 3, avgService: 4, open: true, counters: 2, prefix: "A" },
  { id: 2, name: "Barakah Community Bakery", type: "bakery", status: "limited", density: "yellow", queueSize: 14, waitMins: 2, avgService: 2, open: true, counters: 1, prefix: "B" },
  { id: 3, name: "Al-Nour Water Point", type: "water_point", status: "available", density: "yellow", queueSize: 9, waitMins: 3, avgService: 3, open: true, counters: 3, prefix: "W" },
  { id: 4, name: "Rahma Community Kitchen", type: "community_kitchen", status: "depleted", density: "red", queueSize: 21, waitMins: 4, avgService: 4, open: true, counters: 2, prefix: "K" },
  { id: 5, name: "Student Affairs Office", type: "university_office", status: "available", density: "green", queueSize: 3, waitMins: 5, avgService: 5, open: true, counters: 1, prefix: "S" },
  { id: 6, name: "Civil Registry Office", type: "government_office", status: "limited", density: "yellow", queueSize: 11, waitMins: 3, avgService: 3, open: false, counters: 2, prefix: "G" },
];
