import { type Date as DateInterface, DateClass } from './date';
import { useSessionStore } from 'src/stores/session';
import { fullDateTimeHuman } from 'src/composables/dateUtils';
import { type Ti18NFunction } from './i18n';

const sessionStore = useSessionStore();

interface DateTime extends DateInterface {
  dateTime: string | null;
}

class DateTimeClass extends DateClass implements DateTime {
  // TODO: refactor to formattedDateTime
  dateTime: string | null;

  constructor(t: Ti18NFunction, timestamp: number | null) {
    super(t, timestamp);
    if (timestamp) {
      this.dateTime = fullDateTimeHuman(timestamp, sessionStore.savedDateTimeFormat);
    } else {
      this.dateTime = null;
    }
  }
}

export { type DateTime, DateTimeClass };
