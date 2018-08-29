import React, {Component, PropTypes} from 'react';

import {VpTextField} from 'components';

/**
 * Create a job types table row
 */
export default class JobTypesTableRow extends Component {
  static propTypes = {
    // Row title
    title: PropTypes.string.isRequired,
    onChange: PropTypes.func.isRequired,
    // Field name
    fieldName: PropTypes.string.isRequired,
    // Field value
    value: PropTypes.oneOfType([
      PropTypes.string,
      PropTypes.number
    ])
  };

  /**
   * Only update row on value update
   * @param nextProps
   * @returns {boolean}
   */
  shouldComponentUpdate(nextProps) {
    return this.props.value !== nextProps.value;
  }

  render() {
    const {title, value, onChange, fieldName} = this.props;
    return (
      <tr>
        <td>
          <div>{title}</div>
        </td>
        <td>
            <VpTextField
              value={value}
              onChange={onChange}
              name={fieldName}
              parentClass=""
              hideError
            />
        </td>
      </tr>
    );
  }
}
