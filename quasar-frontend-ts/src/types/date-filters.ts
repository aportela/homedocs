import { date } from "quasar";

interface DateFilterOptionBaseType {
  labelKey: string;
  value: number;
};

const availableSelectOptions = <DateFilterOptionBaseType[]>[
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

interface DateFilterOptionType extends DateFilterOptionBaseType {
  label: string;
};

const dateMask = "YYYY/MM/DD";

interface DateFilter {
  filterType: DateFilterOptionType;
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
  filterType: DateFilterOptionType;
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
    this.filterType = ;
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
    switch (this.filterType?.value) {
      // NONE
      case 0:
        this.formattedDate.from = null;
        this.formattedDate.to = null;
        this.formattedDate.fixed = null;
        break;
      // TODAY
      case 1:
        this.formattedDate.fixed = getFormattedDate();
        break;
      // YESTERDAY
      case 2:
        this.formattedDate.fixed = getFormattedDate(-1);
        break;
      // LAST 7 DAYS
      case 3:
        this.formattedDate.from = getFormattedDate(-7);
        this.formattedDate.to = getFormattedDate();
        break;
      // LAST 15 DAYS
      case 4:
        this.formattedDate.from = getFormattedDate(-15);
        this.formattedDate.to = getFormattedDate();
        break;
      // LAST 31 DAYS
      case 5:
        this.formattedDate.from = getFormattedDate(-31);
        this.formattedDate.to = getFormattedDate();
        break;
      // LAST 365 DAYS
      case 6:
        this.formattedDate.from = getFormattedDate(-365);
        this.formattedDate.to = getFormattedDate();
        break;
      // FIXED DATE
      case 7:
        break;
      // FROM DATE
      case 8:
        //this.formattedDate.from = getFormattedDate();
        break;
      // TO DATE
      case 9:
        //this.formattedDate.to = getFormattedDate();
        break;
      // BETWEEN DATES
      case 10:
        //this.formattedDate.from = getFormattedDate();
        //this.formattedDate.to = getFormattedDate();
        break;
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
};

export { type DateFilterOptionBaseType, type DateFilterOptionType, availableSelectOptions, dateMask, type DateFilter, DateFilterClass };
