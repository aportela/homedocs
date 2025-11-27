interface SearchOnTextEntitiesFilter {
  title: string | null;
  description: string | null;
  notesBody: string | null;
  attachmentsFilename: string | null;
};

interface SearchFilter {
  text: SearchOnTextEntitiesFilter;
  tags: string[];
  dates: {
    createdAt: any;
    lastUpdateAt: any;
    updatedAt: any;
  },
};

export { type SearchOnTextEntitiesFilter, type SearchFilter };
