interface CustomBanner {
  visible: boolean;
  success: boolean,
  error: boolean,
  text: string | null
};

const defaultCustomBanner: CustomBanner = {
  visible: false,
  success: false,
  error: false,
  text: null,
};

export { type CustomBanner, defaultCustomBanner };
