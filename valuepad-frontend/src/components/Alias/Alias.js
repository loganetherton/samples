import React, {Component, PropTypes} from 'react';
import pureRender from 'pure-render-decorator';

import Immutable from 'immutable';

import {VpTextField, Address, PhoneNumber} from 'components';

@pureRender
export default class Alias extends Component {
  static propTypes = {
    // Toggles the alias form display
    isUsingAlias: PropTypes.bool.isRequired,
    // Set prop
    setProp: PropTypes.func.isRequired,
    // Coverage form
    form: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Errors
    errors: PropTypes.instanceOf(Immutable.Map).isRequired
  }

  propPaths = {
    companyName: ['form', 'alias', 'companyName'],
    email: ['form', 'alias', 'email'],
    phone: ['form', 'alias', 'phone'],
    fax: ['form', 'alias', 'fax']
  }

  constructor(props) {
    super(props);
    this.toggleAlias = ::this.toggleAlias;
    this.updateAliasAddress = ::this.updateAliasAddress;
    this.changeState = ::this.changeState;
  }

  /**
   * Toggles the alias form
   */
  toggleAlias() {
    this.props.setProp(!this.props.isUsingAlias, 'isUsingAlias');
  }

  /**
   * Update alias address
   *
   * @param {{target: {name: string, value: string}}} event
   */
  updateAliasAddress(event) {
    this.props.setProp(event.target.value, 'form', 'alias', event.target.name);
  }

  /**
   * Proxy method to update alias state
   *
   * @param {string} state
   */
  changeState(state) {
    this.updateAliasAddress({target: {name: 'state', value: state}});
  }

  render() {
    const {
      isUsingAlias,
      setProp,
      form,
      errors
    } = this.props;

    return (
      <div className="row">
        <div className="col-md-12">
          <label>
            <input type="checkbox" checked={isUsingAlias} onChange={this.toggleAlias} />
            Doing business as:
          </label>
        </div>
        {isUsingAlias &&
          <div className="col-md-12">
            <VpTextField
              label="Company Name"
              placeholder="Company Name"
              value={form.getIn(['alias', 'companyName'])}
              propPath={this.propPaths.companyName}
              setProp={setProp}
              error={errors.get('companyName')}
            />
            <VpTextField
              label="Email"
              placeholder="Email"
              value={form.getIn(['alias', 'email'])}
              propPath={this.propPaths.email}
              setProp={setProp}
              error={errors.get('email')}
            />
            <Address
              formChange={this.updateAliasAddress}
              form={form.get('alias')}
              changeState={this.changeState}
              errors={errors}
            />
            <div className="row">
              <div className="col-md-6">
                <PhoneNumber
                  form={form.get('alias')}
                  setProp={setProp}
                  propPath={this.propPaths.phone}
                  errors={errors}
                  label="phone"
                />
              </div>
              <div className="col-md-6">
                <PhoneNumber
                  form={form.get('alias')}
                  setProp={setProp}
                  propPath={this.propPaths.fax}
                  errors={errors}
                  label="fax"
                />
              </div>
            </div>
          </div>
        }
      </div>
    );
  }
}
