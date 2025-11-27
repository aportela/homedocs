import { type OrderType } from "./order-type";

interface Sort {
  field: string;
  label: string;
  order: OrderType;
}

export { type Sort };
