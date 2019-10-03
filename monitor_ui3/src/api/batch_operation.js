import request from '../utils/request'

export function batch_send_commands(hosts, username, command) {
  return request({
    url: '/batch_operation/send_command',
    method: 'post',
    data: {
      hosts,
      username,
      command
    }
  })
}

export function get_command_result(param) {
  return request({
    url: '/batch_operation/send_command',
    method: 'get',
    params: param
  })
}

export function upload_file(param) {
  return request({
    url: '/batch_operation/upload',
    method: 'post',
    params: param
  })
}
