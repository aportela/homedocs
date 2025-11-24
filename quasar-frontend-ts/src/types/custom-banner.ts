export interface CustomBanner {
  visible: boolean;
  success: boolean,
  error: boolean,
  text: string | null
};

export const defaultCustomBanner: CustomBanner = {
  visible: false,
  success: false,
  error: false,
  text: null,
};
