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

export function read_operation(param) {
  return request({
    url: '/operation/info',
    method: 'get',
    params: param
  })
}

export function add_operation_item(type, form) {
  return request({
    url: '/operation_item/info',
    method: 'post',
    data: {
      type,
      form
    }
  })
}
