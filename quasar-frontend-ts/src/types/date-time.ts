import { type Date as DateInterface } from "./date";

export interface DateTime extends DateInterface {
  dateTime: string | null,
};
