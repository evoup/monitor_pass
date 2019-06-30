import request from '@/utils/request'

export function item_list(param) {
  return request({
    url: '/item/list',
    method: 'get',
    params: param
  })
}

export function change_item_status(id, status) {
  return request({
    url: '/item/status',
    method: 'put',
    data: { id, status }
  })
}
