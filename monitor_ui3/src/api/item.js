import request from '@/utils/request'

export function item_list(param) {
  return request({
    url: '/item/list',
    method: 'get',
    params: param
  })
}

export function change_item_status(id, template_id, status) {
  return request({
    url: '/item/status',
    method: 'put',
    data: { id, template_id, status }
  })
}

export function read_item(param) {
  return request({
    url: '/item/info',
    method: 'get',
    params: param
  })
}

export function add_item(name, key, multiplier, interval, show, unit, desc, template_id) {
  return request({
    url: '/item/info',
    method: 'post',
    data: {
      name,
      key,
      multiplier,
      interval,
      show,
      unit,
      desc,
      template_id
    }
  })
}
