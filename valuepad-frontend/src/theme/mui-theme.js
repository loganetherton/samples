/**
 * Internally, Material-UI components use React's context feature to implement theming.
 * Context is a way to pass down values through the component hierarchy without having to use props at every level.
 * In fact, context is very convenient for concepts like theming, which are usually implemented in a hierarchical manner.
 *
 * There are two recommended ways to apply custom themes: using React lifecycle methods with the context feature, or, using an ES7-style decorator.
 * We use React Lifecycle Methods with Context.
 *
 * Learn more at: http://www.material-ui.com/#/customization/themes
 *
 * Heavily changing the theme color in here is not a good idea since many components are dependant on this palette.
 * Instead, components should be customized individually using theme manager.
 */

//import Colors from 'material-ui/styles/colors';
//import ColorManipulator from 'material-ui/utils/color-manipulator';
import Spacing from 'material-ui/styles/spacing';
import zIndex from 'material-ui/styles/zIndex';

export default {
  spacing: Spacing,
  zIndex: zIndex,
  fontFamily: 'Roboto, sans-serif',
  palette: {
    primary1Color: '#17A1E5',
    //primary2Color: Colors.cyan700,
    //primary3Color: Colors.lightBlack,
    //accent1Color: rgba(23,161,229,0.05),
    //accent2Color: Colors.grey100,
    //accent3Color: Colors.grey500,
    //textColor: Colors.darkBlack,
    //alternateTextColor: Colors.white,
    //canvasColor: Colors.white,
    //borderColor: Colors.grey300,
    //disabledColor: ColorManipulator.fade(Colors.darkBlack, 0.3),
    //pickerHeaderColor: Colors.cyan500,
  }
};
