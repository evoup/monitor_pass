import request from '@/utils/request'

export function template_list(param) {
  return request({
    url: '/template/list',
    method: 'get',
    params: param
  })
}

export function add_template(name, server_groups, templates) {
  return request({
    url: '/template/info',
    method: 'post',
    data: {
      name,
      server_groups,
      templates
    }
  })
}
