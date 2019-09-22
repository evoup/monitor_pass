import Vue from 'vue'
import Vuex from 'vuex'
import app from './modules/app'
import user from './modules/user'
import getters from './getters'

Vue.use(Vuex)

const store = new Vuex.Store({
  modules: {
    app,
    user
  },
  getters,
  // 监控系统应用添加的状态
  state: {
    // 触发器页面的参数附件
    triggerParamComponentName: 'last_eq',
    // 操作页面的操作类型组件
    operationTypeComponentName: 'send_notice'
  }
})

export default store
