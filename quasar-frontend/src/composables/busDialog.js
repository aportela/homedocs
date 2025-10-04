import { useBus } from "src/composables/useBus";

export function useBusDialog() {

  const bus = useBus();

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


  return { onShowDocumentFiles, onShowDocumentNotes };
}
