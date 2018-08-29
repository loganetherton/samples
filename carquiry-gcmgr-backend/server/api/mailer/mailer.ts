import * as sendgrid from 'sendgrid';
import * as fs from 'fs';
import * as path from 'path';
import * as ejs from 'ejs';

export default {
  /**
   * The from address that we'll be using to send emails
   *
   * @var {String}
   */
  from: 'noreply@cardquiry.com',

  /**
   * Set SendGrid API key
   *
   * @param {String} key
   */
  setApiKey: function (key: any) {
    this.apiKey = key;
  },

  /**
   * Send reset password email
   *
   * @param {String} to
   * @param {{resetLink: String}} data
   * @param {Function} callback
   */
  sendResetPasswordEmail: function (to: any, data: any, callback: any) {
    const from = new sendgrid.mail.Email(this.from);
    to = new sendgrid.mail.Email(to);
    const subject = 'Reset Your Password';
    const templatePath = path.resolve(__dirname, '../../../../mail_templates/forgot.html');
    fs.readFile(templatePath, 'utf8', (err: any, template: any) => {
      template = template.replace(/{{{RESET_LINK}}}/gi, data.resetLink);
      const content = new sendgrid.mail.Content('text/html', template);
      const mail = new sendgrid.mail.Mail(from, subject, to, content);

      const sg = require('sendgrid')(this.apiKey);
      const request = sg.emptyRequest({
        method: 'POST',
        path: '/v3/mail/send',
        body: mail.toJSON()
      });

      sg.API(request, callback);
    });
  },

  /**
   * Sends an email to a list of recipients
   *
   * @param {Array} recipients
   * @param {String} subject
   * @param {String} body
   * @param {FUnction} callback
   */
  sendAccountingEmail: function (recipients: any, subject: any, body: any, callback: any) {
    const email = new sendgrid.mail.Mail();
    email.setFrom(new sendgrid.mail.Email(this.from));
    email.setSubject(subject);

    const personalization = new sendgrid.mail.Personalization();
    recipients.forEach((recipient: any) => {
      personalization.addTo(new sendgrid.mail.Email(recipient));
    });
    email.addPersonalization(personalization);

    const templatePath = path.resolve(__dirname, '../../../../mail_templates/accounting.html');
    fs.readFile(templatePath, 'utf8', (err, template) => {
      template = template.replace(/{{{TITLE}}}/gi, subject);
      template = template.replace(/{{{CONTENT}}}/gi, body);

      email.addContent(new sendgrid.mail.Content('text/html', template));

      const sg = require('sendgrid')(this.apiKey);
      const request = sg.emptyRequest({
        method: 'POST',
        path: '/v3/mail/send',
        body: email.toJSON(),
      });

      sg.API(request, callback);
    });
  },

  /**
   * Sends an email regarding unsolved cards
   *
   * @param {Array} recipients
   * @param {Object[]} unsolved
   * @param {String} unsolved[].retailer._id
   * @param {String} unsolved[].retailer.name
   * @param {Number} unsolved[].cardsRemaining
   */
  async sendUnsolvedCardsEmail(recipients: any, unsolved: any) {
    const email = new sendgrid.mail.Mail();
    email.setFrom(new sendgrid.mail.Email(this.from));
    email.setSubject('Unsolved Cards');

    const personalization = new sendgrid.mail.Personalization();
    recipients.forEach((recipient: any) => {
      personalization.addTo(new sendgrid.mail.Email(recipient));
    });
    email.addPersonalization(personalization);

    const templatePath = path.resolve(__dirname, '../../../../mail_templates/unsolved_cards.html');

    try {
      const template: any = await new Promise((resolve, reject): any => {
        fs.readFile(templatePath, 'utf8', (err, template) => {
          if (err) {
            return reject(err);
          } else {
            return resolve(<string>template);
          }
        });
      });

      const emailBody = ejs.render(template, {unsolved});

      email.addContent(new sendgrid.mail.Content('text/html', emailBody));

      // To do: Abstract how the SG mailer is constructed
      const sg = require('sendgrid')(this.apiKey);
      const request = sg.emptyRequest({
        method: 'POST',
        path: '/v3/mail/send',
        body: email.toJSON()
      });

      await sg.API(request);
    } catch (e) {
      this.log(e);
    }
  },

  log(error: any) {
    if (this.logger) {
      this.logger.log(error);
    }
  },

  setLogger(logger: any) {
    this.logger = logger;
  }
};
