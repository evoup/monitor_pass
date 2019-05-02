import request from '@/utils/request'
export function add_user(login_name, name, email, password, desc) {
  return request({
    url: '/user/info',
    method: 'post',
    data: {
      login_name,
      name,
      email,
      password,
      desc
    }
  })
}

export function add_user_group(name, desc, priv, members) {
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

export function user_list(param) {
  return request({
    url: '/user/list',
    method: 'get',
    params: param
  })
}

export function user_group_list(param) {
  return request({
    url: '/user_group/list',
    method: 'get',
    params: param
  })
}

export function user_perm_list(param) {
  return request({
    url: '/user_perm/list',
    method: 'get',
    params: param
  })
}

