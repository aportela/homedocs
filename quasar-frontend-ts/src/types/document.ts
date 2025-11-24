import type { Attachment } from "./attachment";

export interface Document {
  id: string;
  title: string;
  description: string | null;
  attachments: Attachment[];
};
