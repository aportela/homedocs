import type { Attachment as AttachmentInterface } from "./attachment";
import type { Note as NoteInterface } from "./note";
import type { HistoryOperation as HistoryOperationInterface } from "./history-operation";

export interface Document {
  id: string | null;
  title: string | null;
  description: string | null;
  attachments: AttachmentInterface[];
  notes: NoteInterface[];
  historyOperations: HistoryOperationInterface[];
};
