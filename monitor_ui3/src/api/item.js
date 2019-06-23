import request from '@/utils/request'

export function item_list(param) {
  return request({
    url: '/item/list',
    method: 'get',
    params: param
  })
}


