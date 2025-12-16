import { format } from 'quasar';

import { type DateTime as DateTimeInterface } from './dateTime';

interface Attachment {
  id: string;
  name: string;
  hash: string | null;
  size: number;
  humanSize: string;
  createdAt: DateTimeInterface;
  orphaned: boolean;
  shared: boolean;
}

class AttachmentClass implements Attachment {
  id: string;
  name: string;
  hash: string | null;
  size: number;
  humanSize: string;
  createdAt: DateTimeInterface;
  orphaned: boolean;
  shared: boolean;

  constructor(
    id: string,
    name: string,
    hash: string | null,
    size: number,
    createdAt: DateTimeInterface,
    orphaned: boolean,
    shared: boolean,
  ) {
    this.id = id;
    this.name = name;
    this.hash = hash;
    this.size = size;
    this.humanSize = format.humanStorageSize(size);
    this.createdAt = createdAt;
    this.orphaned = orphaned;
    this.shared = shared;
  }
}

export { type Attachment, AttachmentClass };
