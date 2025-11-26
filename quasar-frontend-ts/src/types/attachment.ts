import { type DateTime as DateTimeInterface } from "src/types/date-time";

interface Attachment {
  id: string;
  name: string;
  size: number | null;
  hash: string | null;
  humanSize: string | null;
  createdAt: DateTimeInterface;
  orphaned: boolean;
};

export { type Attachment };
