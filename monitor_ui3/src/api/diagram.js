import request from '@/utils/request'

export function diagram_list(param) {
  return request({
    url: '/diagram/list',
    method: 'get',
    params: param
  })
}
