import request from '@/utils/request'

export function add_trigger(name, expression, desc, enable, level) {
  return request({
    url: '/trigger/info',
    method: 'post',
    data: {
      name,
      expression,
      desc,
      enable,
      level
    }
  })
}

export function trigger_list(param) {
  return request({
    url: '/trigger/list',
    method: 'get',
    params: param
  })
}
