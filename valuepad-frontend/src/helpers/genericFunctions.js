import Immutable from 'immutable';
import moment from 'moment';
import React from 'react';
import {Link} from 'react-router';

import {
  ORDERS_DETAILS,
} from 'redux/modules/urls';

/**
 * Set a default value (such as for a dropdown on load)
 * @param type Reducer constant
 * @param name Prop name
 * @param value Default prop value
 * @returns {{type: *, name: *, value: *}}
 */
export function setDefault(type, name, value) {
  return {
    type,
    name,
    value
  };
}

/**
 * Upload file
 * @param types Reducer types
 * @param docType Document type
 * @param document Document for uploading
 * @param actionType Any additional action identification
 * @returns {{types: *, docType: *, promise: promise, document: *}}
 */
export function fileUpload(types, docType, document, actionType) {
  return {
    promise: client => client.post('dev:/documents', {
      data: {
        file: {
          name: 'document',
          file: document
        }
      }
    }),
    types,
    docType,
    document,
    actionType
  };
}

/**
 * Search appraiser ASC for appraiser sign up
 */
export function searchAppraiserAsc(types, params) {
  return {
    types,
    promise: client => client.get(`dev:/asc?search[licenseNumber]=${params.licenseNumber}&filter[licenseState]=${params.licenseState}`)
  };
}

/**
 * Change a checkbox value into a boolean
 * @param type Props type
 * @param form Form name
 * @param name Form item name
 * @returns {boolean}
 */
export function changeCheckboxImmutable(type, form, name) {
  return !this.props[type].getIn([form, name]);
}

/**
 * Capitalize all words in a string
 * @param input Input string
 * @returns string
 */
export function capitalizeWords(input) {
  return input.replace(/(?:^|\s)\S/g, function(a) {
    return a.toUpperCase();
  });
}

/**
 * Submit the form on enter
 */
export function submitOnEnter(submitFunction) {
  submitFunction();
}

/**
 * Check if password and password confirmation match
 */
export function checkPasswordMatches(errors, form) {
  // Check if password and confirm match
  if (form.get('password') && form.get('confirm') && (form.get('password') !== form.get('confirm'))) {
    return errors.set('confirm', Immutable.List().set(0, 'Password and password confirmation do not match'));
  }
  return errors;
}

/**
 * Determine if a form item is equal during a shouldComponentUpdate call
 * @param form This form
 * @param nextForm Next form
 * @param value Form value sought
 * @returns {boolean}
 */
export function checkFormInequality(form, nextForm, value) {
  if (Array.isArray(value)) {
    return form.getIn(value) !== nextForm.getIn(value);
  }
  if (typeof value === 'string') {
    return form.get(value) !== nextForm.get(value);
  }
  return false;
}

/**
 * Set a property explicitly
 */
export function setProp(type, value, ...name) {
  return {
    type,
    name,
    value
  };
}

/**
 * Store session in localStorage after login success
 */
export function storeSession(res) {
  // Store token
  if (res.token && res.user.id && res.id) {
    localStorage.token = res.token;
    localStorage.userId = res.user.id;
    localStorage.sessionId = res.id;
  } else {
    // Remove token
    localStorage.removeItem('token');
    localStorage.removeItem('userId');
    localStorage.removeItem('sessionId');
  }
}

/**
 * Extract complete date based on date picker and time picker values
 * @param inputMap Input map holding values
 * @param dateValue Value of input Map corresponding to DatePicker
 * @param timeValue Value of input Map correcting to TimePicker
 */
export function extractDate(inputMap, dateValue, timeValue) {
  dateValue = Array.isArray(dateValue) ? dateValue : [dateValue];
  timeValue = Array.isArray(timeValue) ? timeValue : [timeValue];
  // Format schedule
  const scheduledDate = moment(inputMap.getIn(dateValue));
  const scheduledTime = timeValue ? moment(inputMap.get(timeValue)) : moment(inputMap.get(dateValue));
  const year = scheduledDate.year();
  const month = scheduledDate.month();
  const day = scheduledDate.date();
  const hour = scheduledTime.hours();
  const minute = scheduledTime.minutes();
  return {
    year,
    month,
    day,
    hour,
    minute
  };
}

/**
 * Extract a single date value using moment
 * @param form Form which contains date value
 * @param datePath Path to form item
 * @param dateFormat Output format desires
 */
export function extractDateValue(form, datePath, dateFormat) {
  return moment(form.getIn(datePath)).format(dateFormat);
}

/**
 * Extract a
 * @param args
 * @returns {Number}
 */
export function extractDateValueAsInt(...args) {
  return parseInt(extractDateValue(...args), 10);
}

/**
 * Calculate new table width based on column widths
 * @param columnWidths
 * @returns {number}
 */
export function calculateTableWidth(columnWidths) {
  // Calculate total column width
  let totalColumnWidth = 0;
  _.forEach(columnWidths, width => {
    totalColumnWidth = totalColumnWidth + width;
  });
  // Ensure table matches up with column
  return totalColumnWidth;
}

/**
 * Calculate column widths
 * @param tableWidth Initial table width
 * @param columns Column
 * @param minColumnWidth Minimum column width
 */
export function calculateColumnWidths(tableWidth, columns, minColumnWidth) {
  const columnCount = columns.length;
  // Even numbers
  let columnWidth = Math.floor(tableWidth / columnCount);
  let useMin = false;
  // Make sure columns are at least minimum width
  if (columnWidth * columnCount < minColumnWidth * columnCount) {
    columnWidth = minColumnWidth;
    useMin = true;
  }
  // Create each of the column width properties
  const columnWidths = {};
  columns.forEach(column => {
    columnWidths[column] = columnWidth;
  });
  return {
    useMin,
    columnWidths
  };
}

/**
 * Determine if a column should be resizable or not
 * @param index Column index in columns array
 * @param columnCount Total number of columns
 * @returns {boolean}
 */
export function isColumnResizable(index, columnCount) {
  return index + 1 !== columnCount;
}

/**
 * Finish column resize
 * @param newColumnWidth New column width
 * @param columnKey Column ID
 */
export function columnResizeEnd(newColumnWidth, columnKey) {
  const {columnWidths} = this.state;
  // Set the new column width
  columnWidths[columnKey] = newColumnWidth;
  // Set state and update
  this.setState({
    columnWidths
  });
  this.forceUpdate();
}

/**
 * Set table width on browser resize
 * @param browser Browser object
 * @param columns Columns in table
 * @param options Options for minColumnWidth, heightPadding, and widthPadding
 */
export function setTableDimensions(browser, columns, options) {
  // Width
  if (!browser.width) {
    return;
  }
  // Calculate table width in container
  let tableWidth = browser.width - options.widthPadding;
  // Calculate column widths
  const {useMin, columnWidths} = calculateColumnWidths(tableWidth, columns, options.minColumnWidth);
  // Calculate table width
  if (!useMin) {
    tableWidth = calculateTableWidth(columnWidths);
  }

  // Height
  const tableHeight = browser.height - options.heightPadding;

  // Set state for width and height after initial render
  this.setState({
    tableWidth,
    tableHeight,
    columnWidths
  });

  this.forceUpdate();
}

/**
 * Retrieve the borrower from the record
 * @param record Selected record
 * @param contactType Type of contact to search for
 */
export function getContact(record, contactType = 'borrower') {
  const contacts = record.getIn(['property', 'contacts']);
  if (!contacts) {
    return Immutable.Map();
  }
  const thisContact = contacts.filter(contact => contact.get('type') === contactType);
  // Return contact record if we have one
  return thisContact.get(0) ? thisContact.get(0) : Immutable.Map();
}

/**
 * Create notification based on the input notification type
 * ENUM: create-order | update-process-status | delete-order | update-order | bid-request | create-document | delete-document | create-additional-document | delete-additional-document
 */
export function createNotificationByType(notification, selectedOrder, formats, showLabel = false) {
  return (
    <div key={notification.get('id')} className="row">
      <div className="col-md-12">
        <h6>
          <span className="pull-left">{moment(notification.get('createdAt')).format(formats.fullDisplayFormat)}</span>
          {showLabel && notification.get('actionLabel') &&
            <div className="pull-right label label-info" style={{ fontWeight: 'none' }}>{notification.get('actionLabel')}</div>
          }
          <span className="pull-right">
            <Link to={`${ORDERS_DETAILS}/${notification.getIn(['order', 'id'])}`}>{notification.getIn(['order', 'fileNumber'])}</Link>
          </span>
        </h6>
        <div style={{ clear: 'both' }}></div>
        <div style={{ paddingTop: '5px', paddingLeft: 0, paddingRight: 0 }}>
          <p>{notification.get('message')}</p>
        </div>
      </div>
    </div>
  );
}

/**
 * Decorator to only accept numerical input
 * @param target
 * @param name
 * @param descriptor
 * @returns {*}
 */
export function numberOnly(target, name, descriptor) {
  const method = descriptor.value;
  descriptor.value = function(...args) {
    const event = args.shift();
    // Make sure we're getting a number passed in
    if (!/^[\d.]*$/.test(event.target.value)) {
      // Return empty string if first number
      if (!event.target.value.replace(/[^\d.]+/, '').length) {
        event.target.value = '';
      } else {
        return;
      }
    }
    method.call(this, event, ...args);
  };
  return descriptor;
}

/**
 * General function to ensure that all required fields are completed
 */
export function formValidAppraiserSignUp(nextProps, requiredFields) {
  const {setNextButtonDisabled, appraiser} = nextProps;
  let disabled = false;
  if (!disabled) {
    // Iterate fields and find incomplete fields
    requiredFields.forEach(field => {
      field = Array.isArray(field) ? field : [field];
      if (typeof appraiser.getIn(['signUpForm', ...field]) === 'undefined') {
        disabled = true;
        return false;
      }
    });
  }
  setNextButtonDisabled(disabled);
}

/**
 * Generic validation function for appraiser sign up form
 * @param thisProps
 * @param nextProps
 * @param fields Required fields
 */
export function validateSignUpForm(thisProps, nextProps, fields) {
  const {form, setNextButtonDisabled, errors} = thisProps;
  // Force check on initial load
  if (!nextProps) {
    return formValidAppraiserSignUp(thisProps, fields);
  }
  const {form: nextForm, errors: nextErrors} = nextProps;
  // Next button disabled
  if (!Immutable.is(form, nextForm) || !Immutable.is(errors, nextErrors)) {
    // Disable on any errors
    if (nextErrors.toList().count()) {
      setNextButtonDisabled(true);
    } else {
      formValidAppraiserSignUp(nextProps, fields);
    }
  }
}

/**
 * Simply create a query param from incoming object (key -> key, v -> v)
 * @param input
 * @returns {string}
 */
export function createQueryParams(input) {
  if (!input) {
    return '';
  }
  const queryBuilder = [];
  // Take each key in input object and create parameter, setting value to value
  for (const item in input) {
    if (input.hasOwnProperty(item)) {
      if ((typeof input[item] === 'string' && input[item].length) || typeof input[item] === 'number') {
        queryBuilder.push(`${item}=${input[item]}`);
        // Create date
      } else if (['filter[orderedAt]', 'filter[dueDate]', 'filter[acceptedAt]', 'filter[inspectionCompletedAt]',
                  'filter[inspectionScheduledAt]', 'filter[estimatedCompletionDate]', 'filter[putOnHoldAt]',
                  'filter[completedAt]', 'filter[paidAt]', 'filter[revisionReceivedAt]'].indexOf(
          item) !== -1) {
        const thisDate = moment(input[item]);
        if (thisDate.isValid()) {
          queryBuilder.push(`${item}=${moment(input[item]).format('YYYY-MM-DD')}`);
        }
      }
    }
  }
  // No query params
  if (!queryBuilder.length) {
    return '';
  }
  return queryBuilder.join('&');
}

/**
 * Handle select job type
 * @param state
 * @param selectedJobType
 * @returns {*}
 */
export function selectJobType(state, selectedJobType) {
  const selectedJobTypeId = selectedJobType.get('id');
  // Get existing fees
  let existingFees = state.get('fees');
  // See if already set
  const alreadySet = !!existingFees.filter(fee => {
    return fee.getIn(['jobType', 'id']) === selectedJobTypeId;
  }).count();

  // Add to job type fees
  if (!alreadySet) {
    let existingAmount = 0;
    // See if there's a default value
    state.get('fees').forEach(fee => {
      if (fee.getIn(['jobType', 'id']) === selectedJobTypeId) {
        existingAmount = fee.get('amount');
        return false;
      }
    });
    existingFees = existingFees.push(Immutable.fromJS({
      amount: existingAmount,
      jobType: selectedJobType
    }));
  } else {
    let existingFeeIndex;
    existingFees.forEach((fee, index) => {
      if (fee.getIn(['jobType', 'id']) === selectedJobTypeId) {
        existingFeeIndex = index;
        return false;
      }
    });
    if (typeof existingFeeIndex === 'undefined') {
      throw new Error('Could not find job type index to remove from existing job types');
    } else {
      existingFees = existingFees.remove(existingFeeIndex);
    }
  }

  return existingFees;
}

/**
 * Set a job type fee
 * @param state
 * @param action
 */
export function setJobTypeFee(state, action) {
  // Regex for a valid fee value
  const feeValueRegex = /^\d+(\.\d{0,2})?$/;
  let hasError = false;
  // Set error on invalid fee value
  if (!feeValueRegex.test(action.value)) {
    hasError = true;
  }
  // const feeValueProp = action.customerId === DEFAULT_CUSTOMER ? 'fees' : 'customerFees';
  const selectedJobTypeForFee = action.jobType;
  let feeIndex;
  // Find index of fee
  state.get('fees').forEach((fee, index) => {
    if (fee.getIn(['jobType', 'id']) === selectedJobTypeForFee.get('id')) {
      feeIndex = index;
    }
  });
  let stateAfterFeeSet = state
    .setIn(['fees', feeIndex, 'amount'], action.value);
  // Display error
  if (hasError) {
    stateAfterFeeSet = stateAfterFeeSet
      .setIn(['fees', feeIndex, 'error'], true);
  } else {
    stateAfterFeeSet = stateAfterFeeSet
      .removeIn(['fees', feeIndex, 'error']);
  }
  return stateAfterFeeSet;
}

/**
 * Format a string as the user is typing by inserting strings at specific locations
 * @param value Incoming value
 * @param stopLength Length at which to include the replacement
 * @param replacement Replacement string
 */
export function formatStringOnType(value, stopLength, replacement) {
  if (value.length > stopLength - 1) {
    const begin = value.substr(0, stopLength);
    const end = value.substr(stopLength);
    if (!end) {
      value = begin;
    } else {
      value = begin + replacement + end;
    }
  }
  return value;
}

/**
 * Apply default values to job types
 * @param state Current state
 * @param defaultFeesProp Property that holds default fees
 * @param feesProp Property which holds customer fees
 */
export function applyDefaultJobTypeValues(state, defaultFeesProp = 'defaultFees', feesProp = 'fees') {
  const defaultFees = state.get(defaultFeesProp);
  let feesThisClient = state.get(feesProp);
  const customerJobTypes = state.get('customerJobTypes');
  // Default fee map
  let defaultMap = Immutable.Map();
  defaultFees.forEach(fee => {
    defaultMap = defaultMap.set(fee.getIn(['jobType', 'id']), fee.get('amount'));
  });

  // Iterate fees this client, updating those with a default
  feesThisClient = feesThisClient.map(fee => {
    const defaultFee = defaultMap.get(fee.getIn(['jobType', 'local', 'id']));
    if (defaultFee) {
      return fee
        .set('amount', defaultFee)
        .set('removed', false);
    }
    return fee;
  });

  // Create lookup table for already set fees
  let feesThisClientLookup = Immutable.List();
  feesThisClient.forEach(fee => {
    const localId = fee.getIn(['jobType', 'id']);
    if (localId) {
      feesThisClientLookup = feesThisClientLookup.push(localId);
    }
  });

  // Iterate all client job types, pushing a new fee for each except for when a fee is already set
  customerJobTypes.forEach(jobType => {
    const defaultFee = defaultMap.get(jobType.getIn(['local', 'id']));
    if (defaultFee) {
      // Don't already have a fee set, so push a new fee for this job type
      if (feesThisClientLookup.indexOf(jobType.getIn(['id'])) === -1) {
        const defaultFeeThisJobType = defaultMap.get(jobType.getIn(['local', 'id']));
        const feeWithDefaultApplied = Immutable.Map()
          .set('amount', defaultFeeThisJobType)
          .set('jobType', jobType);
        // Push fee into list from backend
        feesThisClient = feesThisClient.push(feeWithDefaultApplied);
      }
    }
  });

  return feesThisClient;
}

/**
 * Update appraiser with sample reports and resume information
 * @param view Which view is currently shown
 * @param triggerError Trigger an error to let the user know what to do
 */
export function updateAppraiserForInvitation(view, triggerError) {
  const {updateAppraiser, invitations, auth} = this.props;
  const userId = this.props.userId ? this.props.userId : auth.getIn(['user', 'id']);
  // Sample reports
  if (view === 'sample-reports') {
    const reports = [];
    // Create array of document identifiers
    invitations.getIn(['sampleReports', 'form']).forEach(report => {
      reports.push({
        id: report.get('id'),
        token: report.get('token')
      });
    });
    // Update appraiser with sample reports
    updateAppraiser(userId, {sampleReports: reports});
    // No reports
    if (!reports.length) {
      return triggerError();
    }
    // Resume
  } else if (view === 'resume') {
    const uploadedResume = invitations.getIn(['resume', 'resume']);
    // No resume uploaded
    if (!uploadedResume) {
      return triggerError();
    }
    // Update appraiser with resume
    updateAppraiser(userId, {
      qualifications: {
        resume: uploadedResume
      }
    });
  }
}

/**
 * Submit job types
 * @param createJobTypeRequest Function to create batch request items
 * @param triggerError Trigger error on invitation dialog
 */
export function submitJobTypesInvitations(createJobTypeRequest, triggerError) {
  const {saveJobTypeFees, auth, invitations} = this.props;
  const fees = invitations.get('fees');
  const userId = this.props.userId ? this.props.userId : auth.getIn(['user', 'id']);
  const selectedInvitation = this.props.selectedInvitation ? this.props.selectedInvitation : invitations.get('selectedInvitation');
  const customerId = selectedInvitation.getIn(['customer', 'id']);
  let dbFeeIds = invitations.get('jobTypesInBackend');
  const feeUpdatePromises = [];
  // See if there are fee errors
  const errors = fees.filter(fee => fee.get('error'));
  // Don't proceed if errors exist
  if (errors.count()) {
    return triggerError(['jobTypesError']);
  }
  // If no job types selected, don't proceed
  if (!fees.count()) {
    return triggerError(['jobTypesErrorNoFees']);
  }
  // Save each fee
  fees.forEach(fee => {
    const feeId = fee.get('id');
    // Remove fee IDs from array
    dbFeeIds = dbFeeIds.filter(fee => fee !== feeId);
    let saveFeePromise;
    let requestFee;
    // Patch
    if (feeId) {
      requestFee = {
        amount: parseFloat(fee.get('amount'))
      };
      saveFeePromise = createJobTypeRequest(userId, customerId, requestFee, 'patch', feeId);
      // Post
    } else {
      requestFee = {
        jobType: fee.getIn(['jobType', 'id']),
        amount: parseFloat(fee.get('amount'))
      };
      saveFeePromise = createJobTypeRequest(userId, customerId, requestFee, 'post');
    }
    feeUpdatePromises.push(saveFeePromise);
  });
  // Delete all remaining
  dbFeeIds.forEach(dbFeeId => {
    feeUpdatePromises.push(createJobTypeRequest(userId, customerId, {}, 'delete', dbFeeId));
  });
  // save all as batch
  saveJobTypeFees(feeUpdatePromises, customerId);
}

/**
 * Opens a new window to download the W9 form
 */
export function downloadW9Form() {
  window.open('https://www.irs.gov/pub/irs-pdf/fw9.pdf', '_blank');
}
