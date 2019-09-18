import request from '../utils/request'

export function operation_list(param) {
  return request({
    url: '/operation/list',
    method: 'get',
    params: param
  })
}
