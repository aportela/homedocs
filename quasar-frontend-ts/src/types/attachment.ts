import { format } from "quasar";

import { type DateTime as DateTimeInterface } from "src/types/date-time";

interface Attachment {
  id: string;
  name: string;
  hash: string | null;
  size: number;
  humanSize: string;
  createdAt: DateTimeInterface;
  orphaned: boolean;
};

class AttachmentClass implements Attachment {
  id: string;
  name: string;
  hash: string | null;
  size: number;
  humanSize: string;
  createdAt: DateTimeInterface;
  orphaned: boolean;

  constructor(id: string, name: string, hash: string | null, size: number, createdAt: DateTimeInterface, orphaned: boolean) {
    this.id = id;
    this.name = name;
    this.hash = hash;
    this.size = size;
    this.humanSize = format.humanStorageSize(size);
    this.createdAt = createdAt;
    this.orphaned = orphaned;
  }
};

export { type Attachment, AttachmentClass };
