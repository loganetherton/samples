/**
 * New Relic agent configuration.
 *
 * See lib/config.defaults.js in the agent distribution for a more complete
 * description of configuration variables and their potential values.
 */
exports.config = {
  /**
   * Array of application names.
   */
  app_name: ['GCMGR'],
  /**
   * Your New Relic license key.
   */
  license_key: '**',
  logging: {
    /**
     * Level at which to log. 'trace' is most useful to New Relic when diagnosing
     * issues with the agent, 'info' and higher will impose the least overhead on
     * production applications.
     */
    level: 'info'
  },
  error_collector: {
    enabled: true,
    ignore_status_codes: [404, 401],
    capture_events: true,
    max_event_samples_stored: 100
  }
};
