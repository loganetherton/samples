export function padLeft(n = '', width = 0, z = '') {
  return n.length >= width ? n : new Array(width - n.length + 1).join(z) + n;
}

/**
 * Capitalize all words in a string
 */
export function capitalizeWords(input) {
  if (typeof input !== 'string') {
    return '';
  }
  return input.replace(/(?:^|\s)\S/g, thisString => thisString.toUpperCase());
}
