import { bus } from "src/boot/bus";

export function useBusDialog() {
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
