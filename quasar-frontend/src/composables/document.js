import { reactive } from "vue";
import { uid, format } from "quasar";
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

  const getNewNote = () => {
    const note = reactive({
      id: uid(),
      body: null,
      createdOnTimestamp: currentTimestamp(),
      creationDate: currentFullDateTimeHuman(),
      creationDateTimeAgo: currentTimeAgo(),
      expanded: false,
      startOnEditMode: true, // new notes start with view mode = "edit" (for allowing input body text)
    });
    return (note);
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

      setFromAPIJSON(data) {
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
          doc.files.push(
            ...JSON.parse(JSON.stringify(data.files)).map((file) => {
              file.creationDate = fullDateTimeHuman(file.createdOnTimestamp);
              file.creationDateTimeAgo = timeAgo(file.createdOnTimestamp);
              file.humanSize = format.humanStorageSize(file.size);
              file.url = "api2/file/" + file.id;
              file.isNew = false;
              file.visible = true;
              return file;
            }),
          );
        }

        doc.notes.length = 0;
        if (Array.isArray(data.notes)) {
          doc.notes.push(
            ...JSON.parse(JSON.stringify(data.notes)).map((note) => {
              note.creationDate = fullDateTimeHuman(note.createdOnTimestamp);
              note.creationDateTimeAgo = timeAgo(note.createdOnTimestamp);
              note.expanded = false;
              note.startOnEditMode = false; // this is only required when adding new note
              return note;
            }),
          );
        }

        doc.historyOperations.length = 0;
        if (Array.isArray(data.history)) {
          doc.historyOperations.push(
            ...JSON.parse(JSON.stringify(data.history)).map((operation) => {
              operation.creationDate = fullDateTimeHuman(
                operation.operationTimestamp,
              );
              operation.creationDateTimeAgo = timeAgo(
                operation.operationTimestamp,
              );
              switch (operation.operationType) {
                case 1:
                  operation.label = "Document created";
                  operation.icon = "post_add";
                  break;
                case 2:
                  operation.label = "Document updated";
                  operation.icon = "edit_note";
                  break;
                default:
                  operation.label = "Unknown operation";
                  operation.icon = "error";
                  break;
              }
              return operation;
            }),
          );
        }
      },

      addFile(id, name, size) {
        const fileId = id || uid();
        doc.files.unshift(
          reactive({
            id: fileId,
            createdOnTimestamp: currentTimestamp(),
            creationDate: currentFullDateTimeHuman(),
            creationDateTimeAgo: currentTimeAgo(),
            name: name,
            size: size || 0,
            hash: null,
            humanSize: size > 0 ? format.humanStorageSize(size) : null,
            url: "api2/file/" + fileId,
            isNew: true,
            visible: true,
          }),
        );
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
          getNewNote()
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
    });

    return doc;
  };

  return { escapeRegExp, getNewNote, getNewDocument };
}
