import request from '@/utils/request'

export function event_list(param) {
  return request({
    url: '/event/list',
    method: 'get',
    params: param
  })
}
