import request from '@/utils/request'

// 读取用户
export function read_user(param) {
  return request({
    url: '/user/info',
    method: 'get',
    params: param
  })
}

// 添加用户
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

// 更新用户,不能更新登录名
export function change_user(id, name, email, old_password, new_password, desc) {
  return request({
    url: '/user/info',
    method: 'put',
    data: {
      id,
      name,
      email,
      old_password,
      new_password,
      desc
    }
  })
}

// 添加用户组
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

// 获取所有用户
export function user_list(param) {
  return request({
    url: '/user/list',
    method: 'get',
    params: param
  })
}

// 用户组列表
export function user_group_list(param) {
  return request({
    url: '/user_group/list',
    method: 'get',
    params: param
  })
}

// 删除用户组
export function delete_user_group(param) {
  return request({
    url: '/user_group/info',
    method: 'delete',
    params: param
  })
}

// 获取所有权限
export function user_perm_list(param) {
  return request({
    url: '/user_perm/list',
    method: 'get',
    params: param
  })
}

export function delete_user(param) {
  return request({
    url: '/user/info',
    method: 'delete',
    params: param
  })
}
