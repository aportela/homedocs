import { type DateTime as DateTimeInterface } from "src/types/date-time";

interface Note {
  id: string;
  body: string;
  createdAt: DateTimeInterface;
  expanded: boolean;
  startOnEditMode: boolean;
};

class NoteClass implements Note {
  id: string;
  body: string;
  createdAt: DateTimeInterface;
  expanded: boolean;
  startOnEditMode: boolean;

  constructor(id: string, body: string, createdAt: DateTimeInterface, expanded: boolean, startOnEditMode: boolean) {
    this.id = id;
    this.body = body;
    this.createdAt = createdAt;
    this.expanded = expanded;
    this.startOnEditMode = startOnEditMode;
  }
};

export { type Note, NoteClass };
