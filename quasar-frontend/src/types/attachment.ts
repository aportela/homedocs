import { format } from "quasar";

import { type DateTime as DateTimeInterface } from "./dateTime";

interface Attachment {
  id: string;
  name: string;
  hash: string | null;
  size: number;
  humanSize: string;
  createdAt: DateTimeInterface;
  orphaned: boolean;
  shareId: string | null;
};

class AttachmentClass implements Attachment {
  id: string;
  name: string;
  hash: string | null;
  size: number;
  humanSize: string;
  createdAt: DateTimeInterface;
  orphaned: boolean;
  shareId: string | null;

  constructor(id: string, name: string, hash: string | null, size: number, createdAt: DateTimeInterface, orphaned: boolean, shareId: string | null) {
    this.id = id;
    this.name = name;
    this.hash = hash;
    this.size = size;
    this.humanSize = format.humanStorageSize(size);
    this.createdAt = createdAt;
    this.orphaned = orphaned;
    this.shareId = shareId;
  }
};

export { type Attachment, AttachmentClass };
