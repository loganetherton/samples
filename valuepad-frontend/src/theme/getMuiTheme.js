import merge from 'lodash.merge';
import Colors from 'material-ui/styles/colors';
import ColorManipulator from 'material-ui/utils/color-manipulator';
import lightBaseTheme from 'material-ui/styles/baseThemes/lightBaseTheme';
import zIndex from 'material-ui/styles/zIndex';

/**
 * Get the MUI theme corresponding to a base theme.
 * It's possible to override the computed theme values
 * by providing a second argument. The calculated
 * theme will be deeply merged with the second argument.
 */
export default function getMuiTheme(baseTheme, muiTheme) {
  baseTheme = merge({}, lightBaseTheme, baseTheme);
  const {palette, spacing} = baseTheme;

  return merge({
    isRtl: false,
    zIndex,
    baseTheme,
    rawTheme: baseTheme, // To provide backward compatibility.
    appBar: {
      color: palette.primary1Color,
      textColor: palette.alternateTextColor,
      height: spacing.desktopKeylineIncrement,
    },
    avatar: {
      borderColor: 'rgba(0, 0, 0, 0.08)',
    },
    badge: {
      color: palette.alternateTextColor,
      textColor: palette.textColor,
      primaryColor: palette.accent1Color,
      primaryTextColor: palette.alternateTextColor,
      secondaryColor: palette.primary1Color,
      secondaryTextColor: palette.alternateTextColor,
    },
    button: {
      height: 36,
      minWidth: 88,
      iconButtonSize: spacing.iconSize * 2,
    },
    cardText: {
      textColor: palette.textColor,
    },
    checkbox: {
      boxColor: palette.textColor,
      checkedColor: palette.primary1Color,
      requiredColor: palette.primary1Color,
      disabledColor: palette.disabledColor,
      labelColor: palette.textColor,
      labelDisabledColor: palette.disabledColor,
    },
    datePicker: {
      color: palette.primary1Color,
      textColor: palette.alternateTextColor,
      calendarTextColor: '#17A1E5',
      selectColor: '#17A1E5',
      selectTextColor: palette.alternateTextColor,
    },
    dropDownMenu: {
      accentColor: palette.borderColor,
    },
    flatButton: {
      color: Colors.transparent,
      buttonFilterColor: '#999999',
      disabledTextColor: ColorManipulator.fade(palette.textColor, 0.3),
      textColor: palette.textColor,
      primaryTextColor: palette.accent1Color,
      secondaryTextColor: palette.primary1Color,
    },
    floatingActionButton: {
      buttonSize: 56,
      miniSize: 40,
      color: palette.accent1Color,
      iconColor: palette.alternateTextColor,
      secondaryColor: palette.primary1Color,
      secondaryIconColor: palette.alternateTextColor,
      disabledTextColor: palette.disabledColor,
    },
    gridTile: {
      textColor: Colors.white,
    },
    inkBar: {
      backgroundColor: palette.accent1Color,
    },
    drawer: {
      width: spacing.desktopKeylineIncrement * 4,
      color: palette.canvasColor,
    },
    listItem: {
      nestedLevelDepth: 18,
    },
    menu: {
      backgroundColor: palette.canvasColor,
      containerBackgroundColor: palette.canvasColor,
    },
    menuItem: {
      dataHeight: 32,
      height: 48,
      hoverColor: 'rgba(0, 0, 0, .035)',
      padding: spacing.desktopGutter,
      selectedTextColor: palette.accent1Color,
    },
    menuSubheader: {
      padding: spacing.desktopGutter,
      borderColor: palette.borderColor,
      textColor: palette.primary1Color,
    },
    paper: {
      backgroundColor: palette.canvasColor,
    },
    radioButton: {
      borderColor: palette.textColor,
      backgroundColor: palette.alternateTextColor,
      checkedColor: palette.primary1Color,
      requiredColor: palette.primary1Color,
      disabledColor: palette.disabledColor,
      size: 24,
      labelColor: palette.textColor,
      labelDisabledColor: palette.disabledColor,
    },
    raisedButton: {
      color: palette.alternateTextColor,
      textColor: palette.textColor,
      primaryColor: palette.accent1Color,
      primaryTextColor: palette.alternateTextColor,
      secondaryColor: palette.primary1Color,
      secondaryTextColor: palette.alternateTextColor,
      disabledColor: ColorManipulator.darken(palette.alternateTextColor, 0.1),
      disabledTextColor: ColorManipulator.fade(palette.textColor, 0.3),
    },
    refreshIndicator: {
      strokeColor: palette.borderColor,
      loadingStrokeColor: palette.primary1Color,
    },
    slider: {
      trackSize: 2,
      trackColor: palette.primary3Color,
      trackColorSelected: palette.accent3Color,
      handleSize: 12,
      handleSizeDisabled: 8,
      handleSizeActive: 18,
      handleColorZero: palette.primary3Color,
      handleFillColor: palette.alternateTextColor,
      selectionColor: palette.primary1Color,
      rippleColor: palette.primary1Color,
    },
    snackbar: {
      textColor: palette.alternateTextColor,
      backgroundColor: palette.textColor,
      actionColor: palette.accent1Color,
    },
    table: {
      backgroundColor: palette.canvasColor,
    },
    tableHeader: {
      borderColor: palette.borderColor,
    },
    tableHeaderColumn: {
      textColor: palette.accent3Color,
      height: 56,
      spacing: 24,
    },
    tableFooter: {
      borderColor: palette.borderColor,
      textColor: palette.accent3Color,
    },
    tableRow: {
      hoverColor: palette.accent2Color,
      stripeColor: ColorManipulator.lighten(palette.primary1Color, 0.55),
      //selectedColor: palette.borderColor,
      selectedColor: 'rgba(23,161,229,0.05)',
      textColor: palette.textColor,
      borderColor: palette.borderColor,
      //borderColor: '#9CD6F3',
      height: 48,
    },
    tableRowColumn: {
      height: 48,
      spacing: 24,
    },
    timePicker: {
      color: palette.alternateTextColor,
      textColor: palette.accent3Color,
      accentColor: palette.primary1Color,
      clockColor: palette.textColor,
      clockCircleColor: palette.clockCircleColor,
      headerColor: palette.pickerHeaderColor || palette.primary1Color,
      selectColor: palette.primary2Color,
      selectTextColor: palette.alternateTextColor,
    },
    toggle: {
      thumbOnColor: palette.primary1Color,
      thumbOffColor: palette.accent2Color,
      thumbDisabledColor: palette.borderColor,
      thumbRequiredColor: palette.primary1Color,
      trackOnColor: ColorManipulator.fade(palette.primary1Color, 0.5),
      trackOffColor: palette.primary3Color,
      trackDisabledColor: palette.primary3Color,
      labelColor: palette.textColor,
      labelDisabledColor: palette.disabledColor,
      trackRequiredColor: ColorManipulator.fade(palette.primary1Color, 0.5),
    },
    toolbar: {
      backgroundColor: '#5F7076',
      height: 75,
      titleFontSize: 20,
      iconColor: 'rgba(0, 0, 0, .40)',
      separatorColor: 'rgba(255,255,255,0.25)',
      menuHoverColor: 'rgba(0, 0, 0, .10)',
    },
    tabs: {
      backgroundColor: palette.primary1Color,
      textColor: ColorManipulator.fade(palette.alternateTextColor, 0.6),
      selectedTextColor: palette.alternateTextColor,
    },
    textField: {
      textColor: palette.textColor,
      hintColor: palette.disabledColor,
      floatingLabelColor: palette.textColor,
      disabledTextColor: palette.disabledColor,
      errorColor: Colors.red500,
      focusColor: palette.primary1Color,
      backgroundColor: 'transparent',
      borderColor: palette.borderColor,
    },
  }, muiTheme);
}
