import request from '@/utils/request'

export function template_list(param) {
  return request({
    url: '/template/list',
    method: 'get',
    params: param
  })
}

export function add_template(param) {
  return request({
    url: '/template/info',
    method: 'post',
    params: param,
    data: {
      param
    }
  })
}
