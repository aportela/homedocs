import { date } from "quasar";

type SelectorOptionTypeValue = 0 | 1 | 2 | 3 | 4 | 5 | 6 | 7 | 8 | 9 | 10;

interface SelectorOption {
  labelKey: string;
  value: SelectorOptionTypeValue;
};

const selectorAvailableOptions = <SelectorOption[]>[
  { labelKey: "Any date", value: 0 },
  { labelKey: "Today", value: 1 },
  { labelKey: "Yesterday", value: 2 },
  { labelKey: "Last 7 days", value: 3 },
  { labelKey: "Last 15 days", value: 4 },
  { labelKey: "Last 31 days", value: 5 },
  { labelKey: "Last 365 days", value: 6 },
  { labelKey: "Fixed date", value: 7 },
  { labelKey: "From date", value: 8 },
  { labelKey: "To date", value: 9 },
  { labelKey: "Between dates", value: 10 },
];

const defaultSelectorOptionValue: SelectorOptionTypeValue = selectorAvailableOptions[0]!.value;

/*
interface DateFilterOptionType extends SelectorOption {
  label: string;
};
*/

// TODO: use storage ????
const dateMask = "YYYY/MM/DD";

interface DateFilter {
  currentType: SelectorOptionTypeValue;
  //filterType: DateFilterOptionType;
  formattedDate: {
    fixed: string | null;
    from: string | null;
    to: string | null;
  }
  timestamps: {
    from: number | null;
    to: number | null;
  }
  state: {
    hasFrom: boolean;
    hasTo: boolean;
    hasFixed: boolean;
    hasValue: boolean;
    denyChanges: boolean;
  },
  // UGLY HACK to skip clearing/reseting values on filterType watchers
  skipClearOnRecalc: {
    from: boolean,
    to: boolean,
    fixed: boolean,
  },
};

class DateFilterClass implements DateFilter {
  currentType: SelectorOptionTypeValue;
  //filterType: DateFilterOptionType;
  formattedDate: {
    fixed: string | null;
    from: string | null;
    to: string | null;
  };
  timestamps: {
    from: number | null;
    to: number | null;
  };
  state: {
    hasFrom: boolean;
    hasTo: boolean;
    hasFixed: boolean;
    hasValue: boolean;
    denyChanges: boolean;
  };
  // UGLY HACK to skip clearing/reseting values on filterType watchers
  skipClearOnRecalc: {
    from: boolean,
    to: boolean,
    fixed: boolean,
  };

  constructor() {
    this.currentType = defaultSelectorOptionValue;
    /*
    this.filterType = {
      label: "",
      labelKey: "",
      value: 0
    };
    */
    this.formattedDate = {
      fixed: null,
      from: null,
      to: null,
    };
    this.timestamps = {
      from: null,
      to: null,
    };
    this.state = {
      hasFrom: false,
      hasTo: false,
      hasFixed: false,
      hasValue: false,
      denyChanges: false,
    };
    this.skipClearOnRecalc = {
      from: false,
      to: false,
      fixed: false,
    };
  }

  getFormattedDate = (daysToSubtract = 0) => {
    return date.formatDate(
      date.addToDate(Date.now(), { days: daysToSubtract }),
      dateMask,
    );
  };

  onRecalcDates = () => {
    if (!this.skipClearOnRecalc.from) {
      this.formattedDate.from = null;
    }
    if (!this.skipClearOnRecalc.to) {
      this.formattedDate.to = null;
    }
    if (!this.skipClearOnRecalc.fixed) {
      this.formattedDate.fixed = null;
    }
    this.skipClearOnRecalc.from = false;
    this.skipClearOnRecalc.to = false;
    this.skipClearOnRecalc.fixed = false;
    this.timestamps.from = null;
    this.timestamps.to = null;
    // generate model/formatted (visible) dates
    //switch (this.filterType?.value) {
    switch (this.currentType) {
      // NONE
      case 0:
        this.formattedDate.from = null;
        this.formattedDate.to = null;
        this.formattedDate.fixed = null;
        break;
      // TODAY
      case 1:
        this.formattedDate.fixed = this.getFormattedDate();
        break;
      // YESTERDAY
      case 2:
        this.formattedDate.fixed = this.getFormattedDate(-1);
        break;
      // LAST 7 DAYS
      case 3:
        this.formattedDate.from = this.getFormattedDate(-7);
        this.formattedDate.to = this.getFormattedDate();
        break;
      // LAST 15 DAYS
      case 4:
        this.formattedDate.from = this.getFormattedDate(-15);
        this.formattedDate.to = this.getFormattedDate();
        break;
      // LAST 31 DAYS
      case 5:
        this.formattedDate.from = this.getFormattedDate(-31);
        this.formattedDate.to = this.getFormattedDate();
        break;
      // LAST 365 DAYS
      case 6:
        this.formattedDate.from = this.getFormattedDate(-365);
        this.formattedDate.to = this.getFormattedDate();
        break;
      // FIXED DATE
      //case 7:
      // FROM DATE
      //case 8:
      // TO DATE
      //case 9:
      // BETWEEN DATES
      //case 10:
    }
  };

  onRecalcTimestamps = () => {
    // generate API timestamps (real filters)
    if (this.formattedDate.fixed) {
      this.timestamps.from = Number(date.formatDate(
        date.adjustDate(
          date.extractDate(this.formattedDate.fixed, dateMask),
          { hour: 0, minute: 0, second: 0, millisecond: 0 },
        ),
        "x", // timestamp in ms
      ));
      this.timestamps.to = Number(date.formatDate(
        date.adjustDate(
          date.extractDate(this.formattedDate.fixed, dateMask),
          { hour: 23, minute: 59, second: 59, millisecond: 999 },
        ),
        "x", // timestamp in ms
      ));
    } else {
      if (this.formattedDate.from) {
        this.timestamps.from = Number(date.formatDate(
          date.adjustDate(
            date.extractDate(this.formattedDate.from, "YYYY/MM/DD"),
            { hour: 0, minute: 0, second: 0, millisecond: 0 },
          ),
          "x", // timestamp in ms
        ));
      }
      if (this.formattedDate.to) {
        this.timestamps.to = Number(date.formatDate(
          date.adjustDate(
            date.extractDate(this.formattedDate.to, "YYYY/MM/DD"),
            { hour: 23, minute: 59, second: 59, millisecond: 999 },
          ),
          "x", // timestamp in ms
        ));
      }
    }
  };

  hasFrom() {
    return (this.state.hasFrom);
  };

  hasTo() {
    return (this.state.hasTo);
  };

  hasFixed() {
    return (this.state.hasFixed);
  };

  setType(type: SelectorOptionTypeValue) {
    this.currentType = type;
    this.state.hasFrom = [3, 4, 5, 6, 8, 10].includes(type);
    this.state.hasTo = [3, 4, 5, 6, 9, 10].includes(type);
    this.state.hasFixed = [1, 2, 7].includes(type);
    this.state.denyChanges = [1, 2, 3, 4, 5, 6].includes(type);
    this.onRecalcDates();
    this.onRecalcTimestamps();
  };
};

export { type SelectorOptionTypeValue, type SelectorOption, selectorAvailableOptions, defaultSelectorOptionValue, dateMask, type DateFilter, DateFilterClass };
