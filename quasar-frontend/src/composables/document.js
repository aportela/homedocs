import { reactive } from "vue";
import { uid } from "quasar";

import { useFormatDates } from "src/composables/formatDate"

export function useDocument() {

  const { currentTimestamp, currentFullDateTimeHuman } = useFormatDates();

  const escapeRegExp = (string) => {
    return string.replace(/[.*+?^=!:${}()|\[\]\/\\]/g, '\\$&');
  };

  const getNewDocument = () => reactive({
    id: null,
    title: null,
    description: null,
    createdOnTimestamp: null,
    lastUpdateTimestamp: null,
    files: [],
    tags: [],
    notes: [],
    history: [],
    hasFiles() {
      return (this.files?.length > 0);
    },
    hasTags() {
      return (this.tags?.length > 0);
    },
    hasNotes() {
      return (this.notes?.length > 0);
    },
    hasHistory() {
      return (this.history?.length > 0);
    },
    reset() {
      this.id = null;
      this.title = null;
      this.description = null;
      this.createdOnTimestamp = null;
      this.lastUpdateTimestamp = null;
      this.files = [];
      this.tags = [];
      this.notes = [];
      this.history = [];
    },
    set(doc) {
      this.id = doc.id;
      this.title = doc.title;
      this.description = doc.description;
      this.createdOnTimestamp = doc.createdOnTimestamp;
      this.lastUpdateTimestamp = doc.lastUpdateTimestamp;
      // TODO: reactive ???????
      this.files = Array.isArray(doc.files) ? JSON.parse(JSON.stringify(doc.files)) : [];
      this.tags = Array.isArray(doc.tags) ? JSON.parse(JSON.stringify(doc.tags)) : [];
      this.notes = Array.isArray(doc.notes) ? JSON.parse(JSON.stringify(doc.notes)) : [];
      this.history = Array.isArray(doc.history) ? JSON.parse(JSON.stringify(doc.history)) : [];
    },
    removeFileAtIdx(index) {
      this.files?.splice(index, 1);
    },
    addNote() {
      this.notes.push(
        reactive(
          {
            id: uid(),
            body: null,
            createdOnTimestamp: currentTimestamp(),
            creationDate: currentFullDateTimeHuman(),
            creationDateTimeAgo: currentTimeAgo(),
            startOnEditMode: true, // new notes start with view mode = "edit" (for allowing input body text)
            visible: true // show always new notes (ignoring filter by body text)
          }
        )
      );
    },
    removeNoteAtIdx(index) {
      this.notes?.splice(index, 1);
    },
    onFilterFiles(text) {
      if (text) {
        this.files.forEach((file) => { file.visible = !!file.name.match(regex); });
      } else {
        this.files.forEach((file) => { file.visible = true; });
      }
    },
    onFilterNotes(text) {
      if (text) {
        document.notes.forEach((note) => { note.visible = !!note.body.match(regex); });
        // TODO: map new fragment with bold
      } else {
        document.notes.forEach((note) => { note.visible = true; });
      }
    }
  });

  return { getNewDocument };
}
