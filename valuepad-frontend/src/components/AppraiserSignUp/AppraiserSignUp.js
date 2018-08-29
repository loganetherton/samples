import React, {Component, PropTypes} from 'react';
import {AppraiserSignUpForm} from 'containers';
import pureRender from 'pure-render-decorator';
import {Void} from 'components';

/**
 * Appraiser sign up
 */
@pureRender
export default class AppraiserSignUp extends Component {
  static propTypes = {
    // Children
    children: PropTypes.object,
    // URL
    location: PropTypes.object
  };

  render() {
    const children = this.props.children;
    let childrenWithProps;
    // Add sign up prop to children
    if (children) {
      childrenWithProps = React.Children.map(children, child => {
        return React.cloneElement(child, { signUp: true });
      });
    }
    return (
      <div ref="appraiser-sign-up">
        <Void pixels={15}/>

        <div className="container-fluid">
          {/*Appraiser sign up*/}
          {!children &&
           <AppraiserSignUpForm
             {...this.props}
           />}
          {/*All subsequent steps*/}
          {!!children && childrenWithProps}
        </div>
      </div>
    );
  }
}
