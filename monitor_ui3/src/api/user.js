import request from '@/utils/request'

export function add_usergroup(name, desc, priv, members) {
  return request({
    url: '/user_group/info',
    method: 'post',
    data: {
      name,
      desc,
      priv,
      members
    }
  })
}

export function user_group_list(param) {
  return request({
    url: '/user_group/list',
    method: 'get',
    param: param
  })
}
