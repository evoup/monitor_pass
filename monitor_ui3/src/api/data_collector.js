import request from '@/utils/request'

export function add_data_collector(name, ip, port) {
  return request({
    url: '/data_collector/info',
    method: 'post',
    data: {
      name, ip, port
    }
  })
}

export function data_collector_list(param) {
  return request({
    url: '/data_collector/list',
    method: 'get',
    params: param
  })
}

export function delete_data_collector(param) {
  return request({
    url: '/data_collector/info',
    method: 'delete',
    params: param
  })
}
