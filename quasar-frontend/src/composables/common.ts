const escapeRegExp = (str: string) => {
  return str.replace(/[.*+?^=!:${}()|[\]/\\]/g, '\\$&');
};

const getRegexForStringMatch = (str: string): RegExp => {
  return new RegExp(escapeRegExp(str), 'i');
};

export { getRegexForStringMatch };
