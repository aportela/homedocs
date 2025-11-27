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

export { type DateFilterOptionBaseType, type DateFilterOptionType, availableSelectOptions, dateMask };
