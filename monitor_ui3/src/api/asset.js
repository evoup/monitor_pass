import request from '@/utils/request'

export function asset_list(param) {
  return request({
    url: '/asset/list',
    method: 'get',
    params: param
  })
}
