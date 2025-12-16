import { type DateTime as DateTimeInterface } from './dateTime';

interface RecentDocumentItem {
  id: string;
  updatedAt: DateTimeInterface;
  title: string;
  description: string | null;
  tags: string[];
  attachmentCount: number;
  noteCount: number;
}

class RecentDocumentItemClass implements RecentDocumentItem {
  id: string;
  updatedAt: DateTimeInterface;
  title: string;
  description: string | null;
  tags: string[];
  attachmentCount: number;
  noteCount: number;

  constructor(
    id: string,
    updatedAt: DateTimeInterface,
    title: string,
    description: string | null,
    tags: string[],
    attachmentCount: number,
    noteCount: number,
  ) {
    this.id = id;
    this.updatedAt = updatedAt;
    this.title = title;
    this.description = description;
    this.tags = tags;
    this.attachmentCount = attachmentCount;
    this.noteCount = noteCount;
  }
}

export { type RecentDocumentItem, RecentDocumentItemClass };
