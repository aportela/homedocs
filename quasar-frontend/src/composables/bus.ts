import { EventBus } from 'quasar';

export interface DocumentPayload {
  document: {
    id: string;
    title: string;
  };
}

export interface BusEvents {
  showDocumentFilesPreviewDialog: DocumentPayload;
  showDocumentNotesPreviewDialog: DocumentPayload;
}

const bus = new EventBus();

const onShowDocumentFiles = (documentId: string, documentTitle: string): void => {
  bus.emit('showDocumentFilesPreviewDialog', {
    document: {
      id: documentId,
      title: documentTitle,
    },
  } as DocumentPayload);
};

const onShowDocumentNotes = (documentId: string | number, documentTitle: string): void => {
  bus.emit('showDocumentNotesPreviewDialog', {
    document: {
      id: documentId,
      title: documentTitle,
    },
  } as DocumentPayload);
};

export { bus, onShowDocumentFiles, onShowDocumentNotes };
