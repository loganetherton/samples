import React, {Component, PropTypes} from 'react';
import Immutable from 'immutable';

import {
  VpPlainDropdown,
  BetterTextField as VpTextField
} from 'components';

// ACH account types
let accountTypes = Immutable.fromJS([
  {name: 'Checking', value: 'checking'},
  {name: 'Savings', value: 'saving'}
]);

/**
 * ACH form
 */
export default class AchForm extends Component {
  static propTypes = {
    // ACH form
    form: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Change form
    formChange: PropTypes.func.isRequired,
    // Change dropdown
    changeDropdown: PropTypes.func.isRequired,
    // Submit form
    submit: PropTypes.func.isRequired,
    // Errors
    errors: PropTypes.instanceOf(Immutable.Map),
    // Show header
    showHeader: PropTypes.bool,
    // AMC user
    isAmc: PropTypes.bool.isRequired,
    // When ACH info is meant to be optional, the account type will default to blank
    isOptional: PropTypes.bool,
    // Is using validate JS
    validateJs: PropTypes.bool,
    // No timeout
    noTimeout: PropTypes.bool
  };

  constructor(props) {
    super(props);

    if (props.isOptional) {
      accountTypes = accountTypes.unshift(Immutable.Map({name: '', value: ''}));
    }
  }

  render() {
    const {
      form,
      formChange,
      changeDropdown,
      submit,
      errors,
      showHeader = false,
      isAmc,
      isOptional = false,
      validateJs = true,
      noTimeout = true
    } = this.props;

    return (
      <div>
        {showHeader && <h3 className="text-center">ACH Information</h3>}
        <div className="row">
          <div className="col-md-6">
            <VpPlainDropdown
              options={accountTypes}
              value={form.get('accountType', isOptional ? '' : 'checking')}
              onChange={changeDropdown}
              name="accountType"
              label="Account Type"
              error={errors.getIn(validateJs ? ['accountType', 0] : ['accountType'])}
            />
          </div>
          <div className="col-md-6">
            <VpTextField
              name="bankName"
              value={form.get('bankName', '')}
              label="Bank Name"
              onChange={formChange}
              enterFunction={submit}
              error={errors.getIn(validateJs ? ['bankName', 0] : ['bankName']) ? 'Bank name is required, and cannot contain spaces' : ''}
              fullWidth
              noTimeout={noTimeout}
            />
          </div>
        </div>
        <div className="row">
          <div className={isAmc ? 'col-md-4' : 'col-md-6'}>
            {!isAmc &&
              <VpTextField
                name="routing"
                value={form.get('routing', '')}
                label="Routing Number"
                onChange={formChange}
                enterFunction={submit}
                error={errors.getIn(validateJs ? ['routing', 0] : ['routing']) ? 'Routing number must be nine digits' : ''}
                fullWidth
                noTimeout={noTimeout}
              />
            }
            {isAmc &&
              <VpTextField
                name="routingNumber"
                value={form.get('routingNumber', '')}
                label="Routing Number"
                onChange={formChange}
                enterFunction={submit}
                error={errors.getIn(validateJs ? ['routingNumber', 0] : ['routingNumber']) ? 'Routing number must be nine digits' : ''}
                fullWidth
                noTimeout={noTimeout}
              />
            }
          </div>
          <div className={isAmc ? 'col-md-4' : 'col-md-6'}>
            <VpTextField
              name="accountNumber"
              value={form.get('accountNumber', '')}
              label="Account Number"
              onChange={formChange}
              enterFunction={submit}
              error={errors.getIn(validateJs ? ['accountNumber', 0] : ['accountNumber']) ? 'Account number can be up to 20 digits' : ''}
              fullWidth
              noTimeout={noTimeout}
            />
          </div>
          {isAmc &&
           <div className="col-md-4">
             <VpTextField
               name="nameOnAccount"
               value={form.get('nameOnAccount', '')}
               label="Name on Account"
               onChange={formChange}
               enterFunction={submit}
               error={errors.getIn(validateJs ? ['nameOnAccount', 0] : ['nameOnAccount']) ? 'Name on account is required and cannot be longer than 22 characters' : ''}
               fullWidth
               noTimeout={noTimeout}
             />
           </div>
          }
        </div>
      </div>
    );
  }
}
