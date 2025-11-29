const allowPreview = (filename: string) => {
  return !!filename?.match(/.(jpg|jpeg|png|gif|mp3|pdf)$/i);
};

const isImage = (filename: string) => {
  if (filename) return !!filename?.match(/.(jpg|jpeg|png|gif)$/i);
};

const isAudio = (filename: string) => {
  return !!filename?.match(/.(mp3)$/i);
};

const isPDF = (filename: string) => {
  return !!filename?.match(/.(pdf)$/i);
};

export { allowPreview, isImage, isAudio, isPDF };
