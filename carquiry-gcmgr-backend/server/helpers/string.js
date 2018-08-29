/**
 * Get the last four characters of a string
 * @param val
 * @return {string}
 */
export function getLastFourCharacters(val) {
  return val.substring(val.length - 4)
}

export function stripDollarSign(str) {
  let value = str.replace(/^\s*\$\s*/, '');
  return value.trim();
}
