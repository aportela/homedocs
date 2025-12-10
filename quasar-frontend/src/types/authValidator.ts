import { type ValidatorField as ValidatorFieldInterface } from "./validatorField";

interface AuthValidator {
  email: ValidatorFieldInterface;
  password: ValidatorFieldInterface;
};

const defaultAuthValidator: AuthValidator = {
  email: {
    hasErrors: false,
    message: null
  },
  password: {
    hasErrors: false,
    message: null
  }
};

export { type AuthValidator, defaultAuthValidator };
