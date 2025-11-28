import { type Date as DateInterface, DateClass } from "./date";
import { dateTimeFormat as localStorageDateTimeFormat } from "src/composables/localStorage";
import { fullDateTimeHuman, } from "/src/composables/dateUtils";
import { type Ti18NFunction } from "./i18n";

interface DateTime extends DateInterface {
  dateTime: string | null,
};

class DateTimeClass extends DateClass implements DateTime {
  // TODO: refactor to formattedDateTime
  dateTime: string | null;

  constructor(t: Ti18NFunction, timestamp: number | null) {
    super(t, timestamp);
    if (timestamp) {
      this.dateTime = fullDateTimeHuman(timestamp, localStorageDateTimeFormat.get());
    } else {
      this.dateTime = null;
    }
  }
};

export { type DateTime, DateTimeClass }
