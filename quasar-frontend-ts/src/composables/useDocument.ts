export function useDocument() {

  const escapeRegExp = (string) => {
    return string.replace(/[.*+?^=!:${}()|\[\]\/\\]/g, "\\$&");
  };

  return { escapeRegExp };
}
