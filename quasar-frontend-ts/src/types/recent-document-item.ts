import { type DateTime as DateTimeInterface } from "./date-time";

interface RecentDocumentItem {
  id: string;
  updatedAt: DateTimeInterface;
  title: string;
  description: string | null;
  tags: string[];
  attachmentCount: number;
  noteCount: number;
};

export { type RecentDocumentItem };
