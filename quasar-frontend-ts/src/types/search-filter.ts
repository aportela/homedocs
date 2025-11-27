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

  constructor(title: string | null, description: string | null, notesBody: string | null, attachmentsFilename: string | null) {
    this.title = title;
    this.description = description;
    this.notesBody = notesBody;
    this.attachmentsFilename = attachmentsFilename;
  }
}

interface SearchDatesFilter {
  createdAt: any;
  lastUpdateAt: any;
  updatedAt: any;
};

class SearchDatesFilterClass implements SearchDatesFilter {
  createdAt: any;
  lastUpdateAt: any;
  updatedAt: any;

  constructor(createdAt: any, lastUpdateAt: any, updatedAt: any) {
    this.createdAt = createdAt;
    this.lastUpdateAt = lastUpdateAt;
    this.updatedAt = updatedAt;
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

  constructor(text: SearchOnTextEntitiesFilter, tags: string[], dates: SearchDatesFilter) {
    this.text = text;
    this.tags = tags;
    this.dates = dates;
  }
}
export { type SearchOnTextEntitiesFilter, SearchOnTextEntitiesFilterClass, type SearchDatesFilter, SearchDatesFilterClass, type SearchFilter, SearchFilterClass };
