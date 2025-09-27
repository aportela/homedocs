import { reactive } from "vue";

export function useDocument() {

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
      this.files = JSON.parse(JSON.stringify(doc.files))
      this.tags = JSON.parse(JSON.stringify(doc.tags))
      this.notes = JSON.parse(JSON.stringify(doc.notes))
      this.history = JSON.parse(JSON.stringify(doc.history))
    }
  });

  return { getNewDocument };
}
