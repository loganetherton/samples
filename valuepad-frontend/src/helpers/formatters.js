export default class formatters {
  static ssn(value) {
    const raw = this.rawNumber(value);
    let formatted = raw;

    if (raw.length > 5) {
      formatted = formatted.replace(/(\d{3})(\d{2})/, '$1-$2-');
    } else if (raw.length > 3) {
      formatted = formatted.replace(/(\d{3})/, '$1-');
    }

    return formatted;
  }

  static tin(value) {
    const raw = this.rawNumber(value);
    let formatted = raw;

    if (raw.length > 2) {
      formatted = formatted.replace(/(\d{2})/, '$1-');
    }

    return formatted;
  }

  static rawNumber(value) {
    return value.replace(/[^\d]*/g, '');
  }
}
