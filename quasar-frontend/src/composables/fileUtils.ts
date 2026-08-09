const allowPreview = (filename: string): boolean => {
  if (filename?.match(/\.(jpg|jpeg|png|gif|mp3)$/i)) {
    return true;
  }

  if (filename?.match(/\.pdf$/i)) {
    return navigator.pdfViewerEnabled === true;
  }

  return false;
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
