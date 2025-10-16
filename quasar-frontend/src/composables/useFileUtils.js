export function useFileUtils() {
  const allowPreview = (filename) => {
    return !!filename?.match(/.(jpg|jpeg|png|gif|mp3|pdf)$/i);
  };

  const isImage = (filename) => {
    if (filename) return !!filename?.match(/.(jpg|jpeg|png|gif)$/i);
  };

  const isAudio = (filename) => {
    return !!filename?.match(/.(mp3)$/i);
  };

  const isPDF = (filename) => {
    return !!filename?.match(/.(pdf)$/i);
  };

  return { allowPreview, isImage, isAudio, isPDF };
}
