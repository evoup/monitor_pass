import request from '@/utils/request'

export function add_trigger(name, expression, desc, enable, level, template_id) {
  return request({
    url: '/trigger/info',
    method: 'post',
    data: {
      name,
      expression,
      desc,
      enable,
      level,
      template_id
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

export function read_trigger(param) {
  return request({
    url: '/trigger/info',
    method: 'get',
    params: param
  })
}
