'use strict'
const merge = require('webpack-merge')
const prodEnv = require('./prod.env')

module.exports = merge(prodEnv, {
  NODE_ENV: '"development"',
  // BASE_API: '"http://192.168.2.8:8000"',
  BASE_API: '"http://localhost/mmsapi2.0"',
})
