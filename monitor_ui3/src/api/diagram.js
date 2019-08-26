import request from '@/utils/request'

export function diagram_list(param) {
  return request({
    url: '/diagram/list',
    method: 'get',
    params: param
  })
}
export function diagram_info(param) {
  return request({
    url: '/diagram/info',
    method: 'get',
    params: param
  })
}

export function server_diagram_list(param) {
  return request({
    url: '/server_diagram/list',
    method: 'get',
    params: param
  })
}
