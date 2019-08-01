import request from '@/utils/request'

export function idc_list(param) {
  return request({
    url: '/idc/list',
    method: 'get',
    params: param
  })
}

export function delete_idc(param) {
  return request({
    url: '/idc/info',
    method: 'delete',
    params: param
  })
}
