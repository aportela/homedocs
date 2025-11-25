import { reactive } from "vue";
import { uid, format } from "quasar";

import { useBus } from "src/composables/useBus";
import { useFormatDates } from "src/composables/useFormatDates";
import { useLocalStorage } from "./useLocalStorage";

import { type Attachment as AttachmentInterface } from "src/types/attachment";
import { type Note as NoteInterface } from "src/types/note";

export function useDocument() {
  const { bus } = useBus();

  const {
    fullDateTimeHuman,
    timeAgo,
    currentTimestamp,
    currentFullDateTimeHuman,
    currentTimeAgo,
  } = useFormatDates();

  const { dateTimeFormat } = useLocalStorage();

  const escapeRegExp = (string) => {
    return string.replace(/[.*+?^=!:${}()|\[\]\/\\]/g, "\\$&");
  };

  const getNewNote = (): NoteInterface => {
    const note = reactive<NoteInterface>({
      id: uid(),
      body: "",
      createdOnTimestamp: currentTimestamp(),
      createdOn: currentFullDateTimeHuman(),
      createdOnTimeAgo: currentTimeAgo(),
      expanded: false,
      startOnEditMode: true, // new notes start with view mode = "edit" (for allowing input body text)
    });
    return note;
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
      attachments: [],
      tags: [],
      notes: [],
      historyOperations: [],

      hasAttachments() {
        return doc.attachments?.length > 0;
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

        doc.attachments.length = 0;
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
          doc.createdOn.dateTime = fullDateTimeHuman(data.createdOnTimestamp, dateTimeFormat.get());
          doc.createdOn.timeAgo = timeAgo(data.createdOnTimestamp);
        } else {
          doc.createdOn.timestamp = null;
          doc.createdOn.dateTime = null;
          doc.createdOn.timeAgo = null;
        }

        if (data.lastUpdateTimestamp) {
          doc.lastUpdate.timestamp = data.lastUpdateTimestamp;
          doc.lastUpdate.dateTime = fullDateTimeHuman(data.lastUpdateTimestamp, dateTimeFormat.get());
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

        doc.attachments.length = 0;
        if (Array.isArray(data.attachments)) {
          doc.attachments.push(
            ...JSON.parse(JSON.stringify(data.attachments)).map((file: AttachmentInterface) => {
              file.createdOn = fullDateTimeHuman(file.createdOnTimestamp, dateTimeFormat.get());
              file.createdOnTimeAgo = timeAgo(file.createdOnTimestamp);
              file.humanSize = format.humanStorageSize(file.size);
              file.url = "api3/attachment/" + file.id;
              file.orphaned = false;
              return file;
            }),
          );
        }

        doc.notes.length = 0;
        if (Array.isArray(data.notes)) {
          doc.notes.push(
            ...JSON.parse(JSON.stringify(data.notes)).map((note) => {
              note.createdOn = fullDateTimeHuman(note.createdOnTimestamp, dateTimeFormat.get());
              note.createdOnTimeAgo = timeAgo(note.createdOnTimestamp);
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
                operation.operationTimestamp, dateTimeFormat.get()
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

      addAttachment(id, name, size) {
        const fileId = id || uid();
        doc.attachments.unshift(
          reactive({
            id: fileId,
            createdOnTimestamp: currentTimestamp(),
            createdOn: currentFullDateTimeHuman(),
            createdOnTimeAgo: currentTimeAgo(),
            name: name,
            size: size || 0,
            hash: null,
            humanSize: size > 0 ? format.humanStorageSize(size) : null,
            url: "api3/attachment/" + fileId,
            orphaned: true, // this property is used for checking if file was uploaded but not associated to document (while not saving document)
          }),
        );
      },

      removeAttachmentAtIdx(index: number) {
        doc.attachments?.splice(index, 1);
      },

      previewAttachment(index: number) {
        if (index >= 0 && index < doc.attachments?.length) {
          bus.emit("showDocumentFilePreviewDialog", {
            document: {
              id: doc.id,
              title: doc.title,
              attachments: doc.attachments,
            },
            currentIndex: index,
          });
        }
      },

      addNote() {
        doc.notes.unshift(getNewNote());
      },

      removeNoteAtIdx(index: number) {
        doc.notes?.splice(index, 1);
      },
    });

    return doc;
  };

  return { escapeRegExp, getNewNote, getNewDocument };
}
