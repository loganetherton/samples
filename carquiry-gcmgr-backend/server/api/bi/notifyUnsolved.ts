import * as moment from 'moment';
import * as socketio from 'socket.io-client';
import * as mongoose from "mongoose";
(<any>mongoose).Promise = require('bluebird');

import {config} from '../../config/environment';

import BiService from './bi.request';
import mailer from '../mailer';
import {ErrorLogger} from '../../loggers';

/**
 * Filters a list of cards based on its age
 *
 * @param {Object[]} cards An array of cards returned from the BI receiver
 * @param {Function} constraint A function that should return false if the card should be excluded
 *                              from the returned object
 * @return {Object} K/v pairs with the retailer's BI ID being the key, and the value is an array of cards
 */
function filterByAge(cards: any, constraint: any) {
  const filtered: any = {};

  cards.forEach((card: any) => {
    if (!filtered[card.retailer_id]) {
      filtered[card.retailer_id] = [];
    }

    const dateAdded = moment(card.date);

    if (constraint(dateAdded)) {
      filtered[card.retailer_id].push(card);
    }
  });

  return filtered;
}

/**
 * Given an object of cards mapped by the retailer's BI ID, filter all the entries with
 * no cards and return an array of objects that represent the remaining cards of each
 * retailer as a result
 *
 * @param {Object} cards
 * @return {Object[]}
 */
function filterByTotal(cards: any) {
  const filtered: any = [];

  Object.entries(cards).forEach((entry: any) => {
    if (entry[1].length) {
      filtered.push({
        retailer: {
          _id: entry[0],
          name: entry[1][0].name
        },
        cardsRemaining: entry[1].length
      });
    }
  });

  return filtered;
}

async function notifyUnsolved() {
  try {
    const begin = moment().subtract(3, 'days').format('YYYY-MM-DD');
    const end = moment().format('YYYY-MM-DD');
    const cards = await BiService.getPendingCards(null, false, begin, end);
    const minimumAge = (dateAdded: any) => {
      return dateAdded.isBefore(moment().subtract(20, 'hours')) && dateAdded.isAfter(moment().subtract(24, 'hours'));
    };
    const minimumAgeForEmails = (dateAdded: any) => {
      return dateAdded.isBefore(moment().subtract(24, 'hours'));
    };

    const notifications = filterByAge(cards, minimumAge);
    const reminderEmails = filterByAge(cards, minimumAgeForEmails);

    const formattedReminders = filterByTotal(reminderEmails);
    const formattedNotifications = filterByTotal(notifications);

    if (formattedReminders.length) {
      const recipients = config.notifyUnsolvedEmail.split(',');
      if (recipients.length) {
        mailer.sendUnsolvedCardsEmail(recipients, formattedReminders);
      }
    }

    config.notifyUnsolvedSockets.forEach((addr: any) => {
      const sock = socketio(addr);

      sock.on('connect', function () {
        sock.emit('unsolved-cards', formattedNotifications);
      });

      sock.on('me too thanks', function () {
        sock.disconnect();
      });
    });
  } catch (e) {
    (new ErrorLogger()).log(e);
  }
}

notifyUnsolved();
