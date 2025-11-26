import { reactive } from "vue";
import { uid, format } from "quasar";
import { type Ti18NFunction } from "./i18n";
import { type DateTime as DateTimeInterface, DateTimeClass } from "src/types/date-time";
import type { Attachment as AttachmentInterface } from "./attachment";
import { type Note as NoteInterface, NoteClass } from "./note";
import { type HistoryOperation as HistoryOperationInterface, HistoryOperationClass } from "./history-operation";
import { type DocumentHistoryOperationResponseItem as DocumentHistoryOperationResponseItemInterface } from "./api-responses";
import {
  fullDateTimeHuman,
  timeAgo,
  currentTimestamp,
  currentFullDateTimeHuman,
  currentTimeAgo
} from "src/composables/useFormatDates";
import { bus } from "src/composables/useBus";
import { dateTimeFormat as localStorageDateTimeFormat } from "src/composables/useLocalStorage";

interface Document {
  id: string | null;
  createdAt: DateTimeInterface | null;
  updatedAt: DateTimeInterface | null;
  title: string | null;
  description: string | null;
  tags: string[];
  attachments: AttachmentInterface[];
  notes: NoteInterface[];
  historyOperations: HistoryOperationInterface[];
};

class DocumentClass implements Document {
  id: string | null;
  createdAt: DateTimeInterface | null;
  updatedAt: DateTimeInterface | null;
  title: string | null;
  description: string | null;
  tags: string[];
  attachments: AttachmentInterface[];
  notes: NoteInterface[];
  historyOperations: HistoryOperationInterface[];

  constructor(
    id: string | null = null,
    createdAt: DateTimeInterface | null = null,
    updatedAt: DateTimeInterface | null = null,
    title: string | null = null,
    description: string | null = null,
    tags: string[] = [],
    attachments: AttachmentInterface[] = [],
    notes: NoteInterface[] = [],
    historyOperations: HistoryOperationInterface[] = []
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
    this.title = null;
    this.description = null;
    this.tags.length = 0;
    this.attachments.length = 0;
    this.notes.length = 0;
    this.historyOperations.length = 0;
  };

  addAttachment = (t: Ti18NFunction, id: string, name: string, size: number, hash: string | null): void => {
    this.attachments.unshift(
      reactive<AttachmentInterface>({
        id: id || uid(),
        name: name,
        size: size,
        humanSize: format.humanStorageSize(size),
        hash: hash,
        createdAt: new DateTimeClass(t, currentTimestamp()),
        orphaned: true, // this property is used for checking if file was uploaded but not associated to document (while not saving document)
      }),
    );
  };

  removeAttachmentAtIdx = (index: number): boolean => {
    if (index >= 0 && index < this.attachments.length) {
      this.attachments.splice(index, 1);
      return true;
    } else {
      console.error("Invalid attachment index", index);
      return false;
    }
  };

  previewAttachment = (index: number): boolean => {
    if (index >= 0 && index < this.attachments.length) {
      bus.emit("showDocumentFilePreviewDialog", {
        document: {
          id: this.id,
          title: this.title,
          attachments: this.attachments,
        },
        currentIndex: index,
      });
      return true;
    } else {
      console.error("Invalid attachment index", index);
      return false;
    }
  };

  addNote = (t: Ti18NFunction): void => {
    this.notes.unshift(
      reactive<NoteClass>(
        new NoteClass(
          uid(),
          null,
          new DateTimeClass(t, currentTimestamp()),
          false,
          true, // new notes start with view mode = "edit" (for allowing input body text)
        ))
    );
  };

  removeNoteAtIdx = (index: number): boolean => {
    if (index >= 0 && index < this.notes.length) {
      this.notes.splice(index, 1);
      return true;
    } else {
      console.error("Invalid note index", index);
      return false;
    }
  };

  setFromAPIJSON(t: Ti18NFunction, data: any) {
    this.id = data.id;
    this.title = data.title;
    this.description = data.description;

    if (data.createdOnTimestamp) {
      this.createdAt = new DateTimeClass(t, data.createdOnTimestamp);
    }

    if (data.lastUpdateTimestamp) {
      this.updatedAt = new DateTimeClass(t, data.lastUpdateTimestamp);
    }

    this.tags.length = 0;
    if (Array.isArray(data.tags) && data.tags.length > 0) {
      this.tags.push(...data.tags);
    }

    this.attachments.length = 0;
    if (Array.isArray(data.attachments) && data.attachments.length > 0) {
      this.attachments.push(
        ...JSON.parse(JSON.stringify(data.attachments)).map((file: AttachmentInterface) => {
          file.createdOn = fullDateTimeHuman(file.createdOnTimestamp, localStorageDateTimeFormat.get());
          file.createdOnTimeAgo = timeAgo(file.createdOnTimestamp);
          file.humanSize = file.size ? format.humanStorageSize(file.size) : null;
          file.orphaned = false;
          return file;
        }),
      );
    }

    this.notes.length = 0;
    if (Array.isArray(data.notes) && data.notes.length > 0) {
      this.notes.push(
        ...data.notes.map((note) =>
          new NoteClass(
            note.id,
            note.body,
            new DateTimeClass(t, note.createdOnTimestamp),
            false,
            false,
          )
        ),
      );
    }

    this.historyOperations.length = 0;
    if (Array.isArray(data.historyOperations) && data.historyOperations.length > 0) {
      this.historyOperations.push(
        ...data.historyOperations.map((operation: DocumentHistoryOperationResponseItemInterface) =>
          new HistoryOperationClass(
            new DateTimeClass(t, operation.createdAtTimestamp),
            operation.operationType
          )
        ),
      );
    }
  };

};

export { type Document, DocumentClass };
