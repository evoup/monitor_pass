import request from '../utils/request'

export function operation_list(param) {
  return request({
    url: '/operation/list',
    method: 'get',
    params: param
  })
}

export function add_operation(name, subject, message, triggerId) {
  return request({
    url: '/operation/info',
    method: 'post',
    data: {
      name,
      subject,
      message,
      triggerId
    }
  })
}
