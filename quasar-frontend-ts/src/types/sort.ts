import { type OrderType } from "./order-type";

interface Sort {
  field: string;
  label: string;
  order: OrderType;
}

class SortClass implements Sort {
  field: string;
  label: string;
  order: OrderType;

  constructor(field: string, label: string, order: OrderType) {
    this.field = field;
    this.label = label;
    this.order = order;
  }
}

export { type Sort, SortClass };
