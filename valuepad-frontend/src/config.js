require('babel/polyfill');

const environment = {
  development: {
    isProduction: false
  },
  production: {
    isProduction: true
  }
}[process.env.NODE_ENV || 'development'];

module.exports = Object.assign({
  host: process.env.HOST || 'localhost',
  port: process.env.PORT,
  app: {
    title: 'ValuePad',
    description: 'ValuePad Web Front-end.',
    meta: {
      charSet: 'utf-8',
      property: {
        'og:site_name': 'ValuePad',
        'og:locale': 'en_US',
        'og:title': 'ValuePad',
        'og:description': 'All Orders, From All Clients, In One Website – Free!',
        //'og:image': 'https://react-redux.herokuapp.com/logo.jpg',
        //'twitter:card': 'summary',
        //'twitter:site': '@erikras',
        //'twitter:creator': '@erikras',
        //'twitter:title': 'React Redux Example',
        //'twitter:description': 'All the modern best practices in one example.',
        //'twitter:image': 'https://react-redux.herokuapp.com/logo.jpg',
        //'twitter:image:width': '200',
        //'twitter:image:height': '200'
      }
    }
  }
}, environment);
