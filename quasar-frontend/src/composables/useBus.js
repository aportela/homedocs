import { EventBus } from 'quasar';

const bus = new EventBus();

export function useBus() {

  const onShowDocumentFiles = (documentId, documentTitle) => {
    bus.emit("showDocumentFilesPreviewDialog", {
      document: {
        id: documentId,
        title: documentTitle
      }
    });
  }

  const onShowDocumentNotes = (documentId, documentTitle) => {
    bus.emit("showDocumentNotesPreviewDialog", {
      document: {
        id: documentId,
        title: documentTitle
      }
    });
  };

  return { bus, onShowDocumentFiles, onShowDocumentNotes };
}
