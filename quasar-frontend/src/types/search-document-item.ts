import { type DateTime as DateTimeInterface } from "./date-time";
import { type Ti18NFunction } from "./i18n";

interface SearchDocumentItemMatchedFragment {
  matchedOn: string;
  fragment: string;
};

interface SearchDocumentItem {
  id: string;
  createdAt: DateTimeInterface;
  updatedAt: DateTimeInterface | null;
  title: string;
  description: string | null;
  tags: string[];
  attachmentCount: number;
  noteCount: number;
  //matchedFragments: SearchDocumentItemMatchedFragment[];
  matchedOnFragment: string | null;
};

const boldStringMatch = (str: string, matchWord: string) => {
  return str.replace(
    new RegExp(matchWord, "gi"),
    (match) => `<strong>${match}</strong>`
  );
};

class SearchDocumentItemClass implements SearchDocumentItem {
  id: string;
  createdAt: DateTimeInterface;
  updatedAt: DateTimeInterface | null;
  title: string;
  description: string | null;
  tags: string[];
  attachmentCount: number;
  noteCount: number;
  //matchedFragments: SearchDocumentItemMatchedFragment[];
  matchedOnFragment: string | null;

  constructor(t: Ti18NFunction, id: string, createdAt: DateTimeInterface, updatedAt: DateTimeInterface, title: string, description: string | null, tags: string[], attachmentCount: number, noteCount: number, matchedFragments: SearchDocumentItemMatchedFragment[], valtoMatch: string) {
    this.id = id;
    this.createdAt = createdAt;
    this.updatedAt = updatedAt;
    this.title = title;
    this.description = description;
    this.tags = tags;
    this.attachmentCount = attachmentCount;
    this.noteCount = noteCount;
    //this.matchedFragments = matchedFragments;
    if (matchedFragments.length > 0 && valtoMatch) {
      this.matchedOnFragment = t("Fast search match fragment",
        {
          fragment: matchedFragments[0]!.fragment ? `${boldStringMatch(matchedFragments[0]!.fragment, valtoMatch)}` : '',
          matchedOn: t(matchedFragments[0]!.matchedOn)
        }
      );
    } else {
      this.matchedOnFragment = null;
    }
  }
};

export { type SearchDocumentItemMatchedFragment, type SearchDocumentItem, SearchDocumentItemClass };
