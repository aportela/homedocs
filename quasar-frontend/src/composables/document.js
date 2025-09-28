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

  const getNewDocument = () => {
    const doc = reactive({
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
        return doc.files?.length > 0;
      },
      hasTags() {
        return doc.tags?.length > 0;
      },
      hasNotes() {
        return doc.notes?.length > 0;
      },
      hasHistoryOperations() {
        return doc.historyOperations?.length > 0;
      },

      reset() {
        doc.id = null;
        doc.title = null;
        doc.description = null;

        doc.createdOn.timestamp = null;
        doc.createdOn.dateTime = null;
        doc.createdOn.timeAgo = null;

        doc.lastUpdate.timestamp = null;
        doc.lastUpdate.dateTime = null;
        doc.lastUpdate.timeAgo = null;

        doc.files.length = 0;
        doc.tags.length = 0;
        doc.notes.length = 0;
        doc.historyOperations.length = 0;
      },

      set(data) {
        doc.id = data.id;
        doc.title = data.title;
        doc.description = data.description;

        if (data.createdOnTimestamp) {
          doc.createdOn.timestamp = data.createdOnTimestamp;
          doc.createdOn.dateTime = fullDateTimeHuman(data.createdOnTimestamp);
          doc.createdOn.timeAgo = timeAgo(data.createdOnTimestamp);
        } else {
          doc.createdOn.timestamp = null;
          doc.createdOn.dateTime = null;
          doc.createdOn.timeAgo = null;
        }

        if (data.lastUpdateTimestamp) {
          doc.lastUpdate.timestamp = data.lastUpdateTimestamp;
          doc.lastUpdate.dateTime = fullDateTimeHuman(data.lastUpdateTimestamp);
          doc.lastUpdate.timeAgo = timeAgo(data.lastUpdateTimestamp);
        } else {
          doc.lastUpdate.timestamp = null;
          doc.lastUpdate.dateTime = null;
          doc.lastUpdate.timeAgo = null;
        }

        doc.tags.length = 0;
        if (Array.isArray(data.tags)) {
          doc.tags.push(...data.tags);
        }

        doc.files.length = 0;
        if (Array.isArray(data.files)) {
          doc.files.push(...JSON.parse(JSON.stringify(data.files)));
        }

        doc.notes.length = 0;
        if (Array.isArray(data.notes)) {
          doc.notes.push(...JSON.parse(JSON.stringify(data.notes)));
        }

        doc.historyOperations.length = 0;
        if (Array.isArray(data.history)) {
          doc.historyOperations.push(
            ...JSON.parse(JSON.stringify(data.history)),
          );
        }
      },

      removeFileAtIdx(index) {
        doc.files?.splice(index, 1);
      },

      previewFile(index) {
        if (index >= 0 && index < doc.files?.length) {
          bus.emit("showDocumentFilePreviewDialog", {
            document: {
              id: doc.id,
              title: doc.title,
              attachments: doc.files,
            },
            currentIndex: index,
          });
        }
      },

      addNote() {
        doc.notes.unshift(
          reactive({
            id: uid(),
            body: null,
            createdOnTimestamp: currentTimestamp(),
            creationDate: currentFullDateTimeHuman(),
            creationDateTimeAgo: currentTimeAgo(),
            expanded: false,
            startOnEditMode: true, // new notes start with view mode = "edit" (for allowing input body text)
            visible: true, // show always new notes (ignoring text filter on note body)
          }),
        );
      },

      removeNoteAtIdx(index) {
        doc.notes?.splice(index, 1);
      },

      filterFiles(text) {
        if (text) {
          const regex = new RegExp(escapeRegExp(text), "i");
          doc.files?.forEach((file) => {
            file.visible = !!file.name?.match(regex);
          });
        } else {
          doc.files?.forEach((file) => {
            file.visible = true;
          });
        }
      },

      filterNotes(text) {
        if (text) {
          const regex = new RegExp(escapeRegExp(text), "i");
          doc.notes?.forEach((note) => {
            note.visible = !!note.body?.match(regex);
            // TODO: map new fragment with bold ?
          });
        } else {
          doc.notes?.forEach((note) => {
            note.visible = true;
          });
        }
      },
    });

    return doc;
  };

  return { getNewDocument };
}
