import { reactive } from "vue";
import { uid } from "quasar";
import { bus } from "src/boot/bus";

import { useFormatDates } from "src/composables/formatDate";

export function useDocument() {
  const {
    fullDateTimeHuman,
    timeAgo,
    currentTimestamp,
    currentFullDateTimeHuman,
    currentTimeAgo,
  } = useFormatDates();

  const escapeRegExp = (string) => {
    return string.replace(/[.*+?^=!:${}()|\[\]\/\\]/g, "\\$&");
  };

  const getNewDocument = () =>
    reactive({
      id: null,
      title: null,
      description: null,
      createdOn: {
        timestamp: null,
        dateTime: null,
        timeAgo: null,
      },
      lastUpdate: {
        timestamp: null,
        dateTime: null,
        timeAgo: null,
      },
      files: [],
      tags: [],
      notes: [],
      historyOperations: [],
      hasFiles() {
        return this.files?.length > 0;
      },
      hasTags() {
        return this.tags?.length > 0;
      },
      hasNotes() {
        return this.notes?.length > 0;
      },
      hasHistoryOperations() {
        return this.historyOperations?.length > 0;
      },
      reset() {
        this.id = null;
        this.title = null;
        this.description = null;
        this.createdOn.timestamp = null;
        this.createdOn.dateTime = null;
        this.createdOn.timeAgo = null;
        this.lastUpdate.timestamp = null;
        this.lastUpdate.dateTime = null;
        this.lastUpdate.timeAgo = null;
        this.files.length = 0;
        this.tags.length = 0;
        this.notes.length = 0;
        this.historyOperations.length = 0;
      },
      set(doc) {
        this.id = doc.id;
        this.title = doc.title;
        this.description = doc.description;
        if (doc.createdOnTimestamp) {
          this.createdOn.timestamp = doc.createdOnTimestamp;
          this.createdOn.dateTime = fullDateTimeHuman(doc.createdOnTimestamp);
          this.createdOn.timeAgo = timeAgo(doc.createdOnTimestamp);
        } else {
          this.createdOn.timestamp = null;
          this.createdOn.dateTime = null;
          this.createdOn.timeAgo = null;
        }
        if (doc.lastUpdateTimestamp) {
          this.lastUpdate.timestamp = doc.lastUpdateTimestamp;
          this.lastUpdate.dateTime = fullDateTimeHuman(doc.lastUpdateTimestamp);
          this.lastUpdate.timeAgo = timeAgo(doc.lastUpdateTimestamp);
        } else {
          this.lastUpdate.timestamp = null;
          this.lastUpdate.dateTime = null;
          this.lastUpdate.timeAgo = null;
        }
        this.tags.length = 0;
        if (Array.isArray(doc.tags)) {
          this.tags.push(...doc.tags);
        }
        this.files.length = 0;
        if (Array.isArray(doc.files)) {
          this.files.push(...JSON.parse(JSON.stringify(doc.files)));
        }
        this.notes.length = 0;
        if (Array.isArray(doc.notes)) {
          this.notes.push(...JSON.parse(JSON.stringify(doc.notes)));
        }
        this.historyOperations.length = 0;
        if (Array.isArray(doc.history)) {
          this.historyOperations.push(
            ...JSON.parse(JSON.stringify(doc.history)),
          );
        }
      },
      removeFileAtIdx(index) {
        this.files?.splice(index, 1);
      },
      previewFile(index) {
        if (index >= 0 && index < this.files?.length) {
          bus.emit("showDocumentFilePreviewDialog", {
            document: {
              id: this.id,
              title: this.title,
              attachments: this.files,
            },
            currentIndex: index,
          });
        }
      },
      addNote() {
        this.notes.push(
          reactive({
            id: uid(),
            body: null,
            createdOnTimestamp: currentTimestamp(),
            creationDate: currentFullDateTimeHuman(),
            creationDateTimeAgo: currentTimeAgo(),
            expanded: false,
            startOnEditMode: true, // new notes start with view mode = "edit" (for allowing input body text)
            visible: true, // show always new notes (ignoring filter by body text)
          }),
        );
      },
      removeNoteAtIdx(index) {
        this.notes?.splice(index, 1);
      },
      filterFiles(text) {
        if (text) {
          const regex = new RegExp(escapeRegExp(text), "i");
          this.files?.forEach((file) => {
            file.visible = !!file.name?.match(regex);
          });
        } else {
          this.files?.forEach((file) => {
            file.visible = true;
          });
        }
      },
      filterNotes(text) {
        if (text) {
          const regex = new RegExp(escapeRegExp(text), "i");
          this.notes.forEach((note) => {
            note.visible = !!note.body?.match(regex);
          });
          // TODO: map new fragment with bold ?
        } else {
          this.notes?.forEach((note) => {
            note.visible = true;
          });
        }
      },
    });

  return { getNewDocument };
}
