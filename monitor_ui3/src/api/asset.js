import request from '@/utils/request'

export function asset_list(param) {
  return request({
    url: '/asset/list',
    method: 'get',
    params: param
  })
}

export function asset_record_list(param) {
  return request({
    url: '/asset_record/list',
    method: 'get',
    params: param
  })
}
