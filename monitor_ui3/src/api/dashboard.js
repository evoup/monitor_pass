import request from '../utils/request'

export function dashboard_server_list(param) {
  return request({
    url: '/dashboard/server/list',
    method: 'get',
    params: param
  })
}
