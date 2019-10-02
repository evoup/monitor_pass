import request from '@/utils/request'

export function read_general_config(param) {
  return request({
    url: '/general_config/info',
    method: 'get',
    params: param
  })
}

export function change_general_config(api_key, send_warn, stop_command, ssh_private_key_dir) {
  return request({
    url: '/general_config/info',
    method: 'put',
    data: {
      api_key,
      send_warn,
      stop_command,
      ssh_private_key_dir
    }
  })
}
