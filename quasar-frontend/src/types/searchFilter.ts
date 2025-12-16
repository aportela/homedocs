import { type DateFilterClass as DateFilterClassInterface, DateFilterClass } from './dateFilters';

interface SearchOnTextEntitiesFilter {
  title: string | null;
  description: string | null;
  notesBody: string | null;
  attachmentsFilename: string | null;
}

class SearchOnTextEntitiesFilterClass implements SearchOnTextEntitiesFilter {
  title: string | null;
  description: string | null;
  notesBody: string | null;
  attachmentsFilename: string | null;

  constructor(
    title?: string | null,
    description?: string | null,
    notesBody?: string | null,
    attachmentsFilename?: string | null,
  ) {
    this.title = title || null;
    this.description = description || null;
    this.notesBody = notesBody || null;
    this.attachmentsFilename = attachmentsFilename || null;
  }
}

interface SearchDatesFilter {
  createdAt: DateFilterClassInterface;
  lastUpdateAt: DateFilterClassInterface;
  updatedAt: DateFilterClassInterface;
}

class SearchDatesFilterClass implements SearchDatesFilter {
  createdAt: DateFilterClassInterface;
  lastUpdateAt: DateFilterClassInterface;
  updatedAt: DateFilterClassInterface;

  constructor(
    createdAt?: DateFilterClassInterface | null,
    lastUpdateAt?: DateFilterClassInterface | null,
    updatedAt?: DateFilterClassInterface | null,
  ) {
    this.createdAt = createdAt || new DateFilterClass();
    this.lastUpdateAt = lastUpdateAt || new DateFilterClass();
    this.updatedAt = updatedAt || new DateFilterClass();
  }
}

interface SearchFilter {
  text: SearchOnTextEntitiesFilter;
  tags: string[];
  dates: SearchDatesFilter;
}

class SearchFilterClass implements SearchFilter {
  text: SearchOnTextEntitiesFilter;
  tags: string[];
  dates: SearchDatesFilter;

  constructor(
    text?: SearchOnTextEntitiesFilter | null,
    tags?: string[] | null,
    dates?: SearchDatesFilter | null,
  ) {
    this.text = text || new SearchOnTextEntitiesFilterClass();
    this.tags = tags || [];
    this.dates = dates || new SearchDatesFilterClass();
  }
}

export {
  type SearchOnTextEntitiesFilter,
  SearchOnTextEntitiesFilterClass,
  type SearchDatesFilter,
  SearchDatesFilterClass,
  type SearchFilter,
  SearchFilterClass,
};
