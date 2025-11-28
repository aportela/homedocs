import { type DateFilterInstance as DateFilterInstanceInterface, DateFilterInstanceClass } from "src/composables/useDateFilter";

interface SearchOnTextEntitiesFilter {
  title: string | null;
  description: string | null;
  notesBody: string | null;
  attachmentsFilename: string | null;
};

class SearchOnTextEntitiesFilterClass implements SearchOnTextEntitiesFilter {
  title: string | null;
  description: string | null;
  notesBody: string | null;
  attachmentsFilename: string | null;

  constructor(title?: string | null, description?: string | null, notesBody?: string | null, attachmentsFilename?: string | null) {
    this.title = title || null;
    this.description = description || null;
    this.notesBody = notesBody || null;
    this.attachmentsFilename = attachmentsFilename || null;
  }
}

interface SearchDatesFilter {
  createdAt: DateFilterInstanceInterface;
  lastUpdateAt: DateFilterInstanceInterface;
  updatedAt: DateFilterInstanceInterface;
};

class SearchDatesFilterClass implements SearchDatesFilter {
  createdAt: DateFilterInstanceInterface;
  lastUpdateAt: DateFilterInstanceInterface;
  updatedAt: DateFilterInstanceInterface;

  constructor(createdAt?: DateFilterInstanceInterface | null, lastUpdateAt?: DateFilterInstanceInterface | null, updatedAt?: DateFilterInstanceInterface | null) {
    this.createdAt = createdAt || new DateFilterInstanceClass();
    this.lastUpdateAt = lastUpdateAt || new DateFilterInstanceClass();
    this.updatedAt = updatedAt || new DateFilterInstanceClass();
  }
}

interface SearchFilter {
  text: SearchOnTextEntitiesFilter;
  tags: string[];
  dates: SearchDatesFilter;
};

class SearchFilterClass implements SearchFilter {
  text: SearchOnTextEntitiesFilter;
  tags: string[];
  dates: SearchDatesFilter;

  constructor(text?: SearchOnTextEntitiesFilter | null, tags?: string[] | null, dates?: SearchDatesFilter | null) {
    this.text = text || new SearchOnTextEntitiesFilterClass();
    this.tags = tags || [];
    this.dates = dates || new SearchDatesFilterClass();
  }
}

export { type SearchOnTextEntitiesFilter, SearchOnTextEntitiesFilterClass, type SearchDatesFilter, SearchDatesFilterClass, type SearchFilter, SearchFilterClass };
