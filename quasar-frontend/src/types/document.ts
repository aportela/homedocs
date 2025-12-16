import { bus } from 'src/composables/bus';
import { type Ti18NFunction } from './i18n';
import { type DateTime as DateTimeInterface, DateTimeClass } from './dateTime';
import { type Attachment as AttachmentInterface, AttachmentClass } from './attachment';
import { type Note as NoteInterface, NoteClass } from './note';
import {
  type HistoryOperation as HistoryOperationInterface,
  HistoryOperationClass,
} from './historyOperation';
import {
  type DocumentHistoryOperationResponseItem as DocumentHistoryOperationResponseItemInterface,
  type GetDocumentResponse as GetDocumentResponseInterface,
} from './apiResponses';

interface Document {
  id: string | null;
  createdAt: DateTimeInterface | null;
  updatedAt: DateTimeInterface | null;
  title: string;
  description: string;
  tags: string[];
  attachments: AttachmentInterface[];
  notes: NoteInterface[];
  historyOperations: HistoryOperationInterface[];
}

class DocumentClass implements Document {
  id: string | null;
  createdAt: DateTimeInterface | null;
  updatedAt: DateTimeInterface | null;
  title: string;
  description: string;
  tags: string[];
  attachments: AttachmentInterface[];
  notes: NoteInterface[];
  historyOperations: HistoryOperationInterface[];

  constructor(
    id: string | null = null,
    createdAt: DateTimeInterface | null = null,
    updatedAt: DateTimeInterface | null = null,
    title: string = '',
    description: string = '',
    tags: string[] = [],
    attachments: AttachmentInterface[] = [],
    notes: NoteInterface[] = [],
    historyOperations: HistoryOperationInterface[] = [],
  ) {
    this.id = id;
    this.createdAt = createdAt;
    this.updatedAt = updatedAt;
    this.title = title;
    this.description = description;
    this.tags = tags;
    this.attachments = attachments;
    this.notes = notes;
    this.historyOperations = historyOperations;
  }

  hasTags = (): boolean => {
    return this.tags.length > 0;
  };

  hasAttachments = (): boolean => {
    return this.attachments.length > 0;
  };

  hasNotes = (): boolean => {
    return this.notes.length > 0;
  };

  hasHistoryOperations = (): boolean => {
    return this.historyOperations.length > 0;
  };

  reset = (): void => {
    this.id = null;
    this.createdAt = null;
    this.updatedAt = null;
    this.title = '';
    this.description = '';
    this.tags.length = 0;
    this.attachments.length = 0;
    this.notes.length = 0;
    this.historyOperations.length = 0;
  };

  previewAttachment = (index: number): boolean => {
    if (index >= 0 && index < this.attachments.length) {
      bus.emit('showDocumentFilePreviewDialog', {
        document: {
          id: this.id,
          title: this.title,
          attachments: this.attachments,
        },
        currentIndex: index,
      });
      return true;
    } else {
      console.error('Invalid attachment index', index);
      return false;
    }
  };

  parseJSONResponse(t: Ti18NFunction, response: GetDocumentResponseInterface) {
    this.id = response.data.document.id;
    this.title = response.data.document.title;
    this.description = response.data.document.description || '';
    this.createdAt = new DateTimeClass(t, response.data.document.createdAtTimestamp);
    if (response.data.document.updatedAtTimestamp) {
      this.updatedAt = new DateTimeClass(t, response.data.document.updatedAtTimestamp);
    } else {
      this.updatedAt = null;
    }
    this.tags.length = 0;
    if (response.data.document.tags.length > 0) {
      this.tags.push(...response.data.document.tags);
    }
    this.attachments.length = 0;
    if (response.data.document.attachments.length > 0) {
      this.attachments.push(
        ...response.data.document.attachments.map(
          (attachment) =>
            new AttachmentClass(
              attachment.id,
              attachment.name,
              attachment.hash,
              attachment.size,
              new DateTimeClass(t, attachment.createdAtTimestamp),
              false,
              attachment.shared,
            ),
        ),
      );
    }
    this.notes.length = 0;
    if (response.data.document.notes.length > 0) {
      this.notes.push(
        ...response.data.document.notes.map(
          (note) =>
            new NoteClass(
              note.id,
              note.body,
              new DateTimeClass(t, note.createdAtTimestamp),
              false,
              false,
            ),
        ),
      );
    }
    this.historyOperations.length = 0;
    if (response.data.document.historyOperations.length > 0) {
      this.historyOperations.push(
        ...response.data.document.historyOperations.map(
          (operation: DocumentHistoryOperationResponseItemInterface) =>
            new HistoryOperationClass(
              new DateTimeClass(t, operation.createdAtTimestamp),
              operation.operationType,
            ),
        ),
      );
    }
  }
}

export { type Document, DocumentClass };
