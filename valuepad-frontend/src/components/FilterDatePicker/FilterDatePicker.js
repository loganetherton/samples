import React, {Component, PropTypes} from 'react';
import DatePicker from 'react-datepicker';
import moment from 'moment';

export default class FilterDatePicker extends Component {
  static propTypes = {
    // Change function
    changeHandler: PropTypes.func.isRequired,
    // form
    form: PropTypes.object,
    // Prop name
    name: PropTypes.oneOfType([
      PropTypes.string,
      PropTypes.array
    ]),
    // placeholder text
    placeholderText: PropTypes.string,
    // class name
    className: PropTypes.string,
    // date format
    dateFormat: PropTypes.string,
  };

  /**
   * Handle selecting a date
   * @param event Always null
   * @param value Value
   */
  handleSelect(value) {
    this.props.changeHandler(value);
  }

  render() {
    const {
      form,
      name,
      placeholderText = 'Search',
      className = 'filter-date-picker',
      dateFormat = 'M/D/YYYY'
    } = this.props;
    let value = Array.isArray(name) ? form.getIn(name) : form.get(name);
    if (!value) {
      value = null;
    } else {
      value = moment(value);
    }

    return (
      <DatePicker
        placeholderText={ placeholderText }
        selected={ value }
        className={ className }
        dateFormat={ dateFormat }
        onChange={ this.handleSelect.bind(this)}
      />
    );
  }
}
