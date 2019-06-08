import request from '@/utils/request'

export function template_list(param) {
  return request({
    url: '/template/list',
    method: 'get',
    params: param
  })
}
