import { type ValidatorField as ValidatorFieldInterface } from "src/types/validator-field";

export interface AuthValidator {
  email: ValidatorFieldInterface;
  password: ValidatorFieldInterface;
};

export const defaultAuthValidator: AuthValidator = {
  email: {
    hasErrors: false,
    message: null
  },
  password: {
    hasErrors: false,
    message: null
  }
};
