import { computed, reactive, watch } from "vue";
import { useI18n } from "vue-i18n";
import { date } from "quasar";
import { type DateFilterSelectorOption as DateFilterSelectorOptionInterface, availableSelectOptions, dateMask } from "src/types/date-filters";

class DateFilterInstanceClass implements DateFilterInstance {
  //filterType: DateFilterTypeOption;
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
    from: boolean;
    to: boolean;
    fixed: boolean;
  };

  constructor() {
    //this.filterType = filterType; //dateFilterTypeOptions.value[0]!;
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
    // UGLY HACK to skip clearing/reseting values on filterType watchers
    this.skipClearOnRecalc = {
      from: false,
      to: false,
      fixed: false,
    };
  }

  onRecalcDates() {
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

  onRecalcTimestamps() {
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

interface DateFilterInstance {
  //filterType: DateFilterTypeOption;
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
    from: boolean;
    to: boolean;
    fixed: boolean;
  };
};

const getFormattedDate = (daysToSubtract = 0) => {
  return date.formatDate(
    date.addToDate(Date.now(), { days: daysToSubtract }),
    dateMask,
  );
};


export function useDateFilter() {
  const { t } = useI18n();

  const dateFilterTypeOptions = computed(() =>
    availableSelectOptions.map((option: DateFilterSelectorOptionInterface) => ({
      ...option,
      label: t(option.labelKey),
    })),
  );

  const getDateFilterInstance = () => {
    const dateFilter: DateFilterInstanceClass = reactive<DateFilterInstanceClass>(new DateFilterInstanceClass());

    /*

watch(
  () => dateFilter.formattedDate.from,
  (value) => {
    dateFilter.state.hasValue = !!value;
    dateFilter.onRecalcTimestamps();
  },
);

watch(
  () => dateFilter.formattedDate.to,
  (value) => {
    dateFilter.state.hasValue = !!value;
    dateFilter.onRecalcTimestamps();
  },
);

watch(
  () => dateFilter.formattedDate.fixed,
  (value) => {
    dateFilter.state.hasValue = !!value;
    dateFilter.onRecalcTimestamps();
  },
);


// UGLY HACK: selected value (model with label/value) do not react to global i18n changes
// so we are watching changes on first option label and when this label translation changes
// we translate again the label assigned to the model
watch(
() => dateFilterTypeOptions.value[0]!.label,
() => {
dateFilter.filterType.label = t(dateFilter.filterType.labelKey);
},
);

watch(
() => dateFilter.filterType,
(filterType) => {
if (filterType) {
  dateFilter.state.hasFrom = [3, 4, 5, 6, 8, 10].includes(
    filterType.value,
  );
  dateFilter.state.hasTo = [3, 4, 5, 6, 9, 10].includes(
    filterType.value,
  );
  dateFilter.state.hasFixed = [1, 2, 7].includes(filterType.value);
  dateFilter.state.denyChanges = [1, 2, 3, 4, 5, 6].includes(
    filterType.value,
  );
} else {
  dateFilter.filterType = dateFilterTypeOptions.value[0]!;
}
dateFilter.onRecalcDates();
dateFilter.onRecalcTimestamps();
},
);

return dateFilter;
};
*/
  }
  return {
    dateFilterTypeOptions,
    getDateFilterInstance,
  };
};

//export { type DateFilterInstance, DateFilterInstanceClass };
